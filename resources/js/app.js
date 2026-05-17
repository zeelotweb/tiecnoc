import * as FilePond from 'filepond';
import 'filepond/dist/filepond.min.css';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css';
import FilePondPluginImageResize from 'filepond-plugin-image-resize';
import FilePondPluginImageTransform from 'filepond-plugin-image-transform';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import heic2any from "heic2any";
//FilePond.registerPlugin(FilePondPluginImagePreview);


FilePond.registerPlugin(
    FilePondPluginImagePreview,
    FilePondPluginImageResize,
    FilePondPluginImageTransform,
    FilePondPluginFileValidateType
);

window.FilePond = FilePond;




/*
|--------------------------------------------------------------------------
| PRODUCT INITIALIZER (NEW STANDARD) FOR MARCHENDIZE
|--------------------------------------------------------------------------
*/









    document.addEventListener('alpine:init', () => {
        window.addEventListener('toggle-theme', () => {
            let theme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', theme);
        });
    });








/**
 * SURGICAL HELPER: Unified Chunked Upload
 */
async function postMediaChunkUpload(file, csrf, progress) {
    const uploadId = crypto.randomUUID();
    const CHUNK_SIZE = 5 * 1024 * 1024;
    const totalChunks = Math.ceil(file.size / CHUNK_SIZE);

    for (let i = 0; i < totalChunks; i++) {
        const chunk = file.slice(i * CHUNK_SIZE, Math.min((i + 1) * CHUNK_SIZE, file.size));

        const fd = new FormData();
        fd.append('upload_id', uploadId);
        fd.append('chunk_index', i);
        fd.append('file', chunk);

        const res = await fetch('/admin/upload/chunk', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf },
            body: fd
        });

        if (!res.ok) throw new Error('Chunk failed');

        progress(true, i + 1, totalChunks);
    }

    const finalize = await fetch('/admin/upload/complete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf
        },
        body: JSON.stringify({
            upload_id: uploadId,
            total_chunks: totalChunks,
            filename: file.name
        }),
    });

    return await finalize.json();
}

/**
 * FINAL FIXED INITIALIZER
 */
window.initPostMediaPond = function (rootEl) {

    const input = rootEl.querySelector('#post-uploader, #circle-uploader');
    const submitBtn = rootEl.querySelector('#submitBtn');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!input || input._filepond) return;

    const pond = FilePond.create(input, {
        allowMultiple: true,
        name: 'media_files',
        maxFileSize: '200MB',
        acceptedFileTypes: [
            'image/jpeg', 'image/png', 'image/webp', 'image/heic',
            'video/mp4', 'video/quicktime',
            'audio/mpeg'
        ],

        beforeAddFile: async (fileItem) => {
            const file = fileItem.file;

            if (file.name.toLowerCase().endsWith('.heic') || file.type === 'image/heic') {
                try {
                    const converted = await heic2any({
                        blob: file,
                        toType: 'image/jpeg',
                        quality: 0.8
                    });

                    const newFile = new File(
                        [converted],
                        file.name.replace(/\.[^/.]+$/, ".jpg"),
                        { type: 'image/jpeg' }
                    );

                    pond.addFile(newFile);
                    return false;

                } catch (e) {
                    return true;
                }
            }

            return true;
        },

        server: {
            process: async (fieldName, file, metadata, load, error, progress) => {
                try {
                    const result = await postMediaChunkUpload(file, csrf, progress);
                    load(result.filename || result.path);
                } catch (err) {
                    error(err.message);
                }
            },
            revert: {
                url: '/admin/upload/revert',
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf }
            }
        }
    });

    function syncState() {
        const files = pond.getFiles().filter(f => f.serverId);

        const payload = JSON.stringify(files.map((f, i) => ({
            file: f.serverId,
            title: f.getMetadata('title') || '',
            description: f.getMetadata('description') || '',
            sort_order: i,
        })));

        console.log('PAYLOAD →', payload);

        // ✅ CORRECT COMPONENT TARGETING
        const wireId = rootEl.closest('[wire\\:id]')?.getAttribute('wire:id');

        if (wireId) {
            window.Livewire.find(wireId).set('media', payload);
        }

        // optional Alpine sync
        rootEl.__x?.$data && (rootEl.__x.$data.media = payload);

        const isProcessing = pond.getFiles().some(
            f => f.status === FilePond.FileStatus.PROCESSING
        );

        if (submitBtn) submitBtn.disabled = isProcessing;
    }

    pond.on('processfiles', syncState);
    pond.on('removefile', syncState);

    window.addEventListener('resetPostMediaPond', () => {
        pond.removeFiles();

        const wireId = rootEl.closest('[wire\\:id]')?.getAttribute('wire:id');

        if (wireId) {
            window.Livewire.find(wireId).set('media', '');
        }

        rootEl.__x?.$data && (rootEl.__x.$data.media = '');
    });

    input._filepond = pond;
};