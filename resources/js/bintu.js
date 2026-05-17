/**
window.initGalleryChunkPond = () => {
    const input = document.querySelector('#gallery-uploader');
    if (!input) return;

    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    FilePond.create(input, {
        allowMultiple: true,
        maxFiles: 10,

        allowFileTypeValidation: true,
        acceptedFileTypes: [
            'image/*',
            'video/*',
            'application/pdf',
            'image/gif',
            'video/quicktime', // .mov
        ],

        labelIdle: '<span class="text-[10px] uppercase tracking-[0.3em] font-bold">Upload Media</span>',

        server: {
            process: async (fieldName, file, metadata, load, error, progress) => {

                const uploadId = crypto.randomUUID();
                const CHUNK_SIZE = 5 * 1024 * 1024;
                const totalChunks = Math.ceil(file.size / CHUNK_SIZE);

                try {
                    // 🔹 SEND CHUNKS
                    for (let i = 0; i < totalChunks; i++) {

                        const chunk = file.slice(
                            i * CHUNK_SIZE,
                            Math.min((i + 1) * CHUNK_SIZE, file.size)
                        );

                        const fd = new FormData();
                        fd.append('upload_id', uploadId);
                        fd.append('chunk_index', i);
                        fd.append('file', chunk);

                        const res = await fetch('/admin/media/vault/upload/chunk', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrf },
                            body: fd
                        });

                        if (!res.ok) throw new Error('Chunk upload failed');

                        progress(true, i + 1, totalChunks);
                    }

                    // 🔹 FINALIZE
                    const finalize = await fetch('/admin/media/vault/upload/complete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify({
                            upload_id: uploadId,
                            total_chunks: totalChunks,
                            filename: file.name
                        })
                    });

                    const result = await finalize.json();

                    if (!finalize.ok) throw new Error(result.error || 'Finalize failed');

                    // 🚨 THIS LINE STOPS THE SPINNER
                    load(result.path);

                } catch (err) {
                    console.error(err);
                    error(err.message);
                }
            }
        }
    });
};



window.initProductGalleryPond = () => {
    const input = document.querySelector('#gallery-uploader');
    if (!input || input.dataset.pondInit) return;

    input.dataset.pondInit = true;

    FilePond.create(input, {
        allowMultiple: true,
        maxFiles: 10,

        acceptedFileTypes: [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif',
            'image/svg+xml',
            'video/mp4',
            'video/quicktime',
            'video/webm',
            'video/ogg',
            'application/pdf'
        ],

        server: {
            process: postMediaChunkUpload,   // ✅ CHUNK ENGINE
            revert: null
        }
    });
     // createPond('#gallery-uploader');
};
**/
