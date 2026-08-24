// UPPY — colorway image uploads (product-color front/back). Ported from
// taongaf's resources/js/media-uppy.js: same headless-core, custom-transport,
// 100% custom Alpine UI shape, trimmed to the one job this needs — a single
// image per call, no staging grid, no crop tool. Hits tiecnoc's existing
// generic /admin/upload/chunk + /admin/upload/complete (ChunkUploadController),
// which assemble to temp/{finalName} and return {filename, mime, size} — the
// visuals tool moves that temp file into its permanent location on save.
import Uppy from '@uppy/core';
import heic2any from 'heic2any';

const CHUNK_SIZE = 5 * 1024 * 1024;
const MAX_SIZE = 10 * 1024 * 1024; // matches ChunkUploadController's chunk-level image expectations
const IMAGE_MAX_DIMENSION = 1600;
const IMAGE_QUALITY = 0.85;

// Same recipe as media-uppy.js's resizeImage in taongaf — contain within
// 1600x1600, re-encode as JPEG @ 85, via plain canvas.
function resizeImage(file) {
    return new Promise((resolve) => {
        const img = new Image();
        img.onload = () => {
            let { width, height } = img;
            const scale = Math.min(1, IMAGE_MAX_DIMENSION / Math.max(width, height));
            width = Math.round(width * scale);
            height = Math.round(height * scale);

            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            canvas.getContext('2d').drawImage(img, 0, 0, width, height);
            URL.revokeObjectURL(img.src);

            canvas.toBlob(
                (blob) => resolve(blob
                    ? new File([blob], file.name.replace(/\.[^/.]+$/, '.jpg'), { type: 'image/jpeg' })
                    : file),
                'image/jpeg',
                IMAGE_QUALITY,
            );
        };
        img.onerror = () => resolve(file);
        img.src = URL.createObjectURL(file);
    });
}

async function prepareImage(file) {
    const isHeic = file.name.toLowerCase().endsWith('.heic') || file.type === 'image/heic';

    if (isHeic) {
        try {
            const converted = await heic2any({ blob: file, toType: 'image/jpeg', quality: IMAGE_QUALITY });
            file = new File([converted], file.name.replace(/\.[^/.]+$/, '.jpg'), { type: 'image/jpeg' });
        } catch {
            return file; // fall through with the original — graceful degrade
        }
    }

    return resizeImage(file);
}

async function uploadChunked(file, { csrf, onProgress }) {
    const uploadId = crypto.randomUUID();
    const totalChunks = Math.ceil(file.size / CHUNK_SIZE);

    for (let i = 0; i < totalChunks; i++) {
        const chunk = file.slice(i * CHUNK_SIZE, Math.min((i + 1) * CHUNK_SIZE, file.size));
        const fd = new FormData();
        fd.append('upload_id', uploadId);
        fd.append('chunk_index', i);
        fd.append('file', chunk);

        const res = await fetch('/admin/upload/chunk', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: fd,
        });

        if (!res.ok) throw new Error(`Chunk ${i} failed`);
        onProgress?.(Math.round(((i + 1) / totalChunks) * 100));
    }

    const completeRes = await fetch('/admin/upload/complete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ upload_id: uploadId, total_chunks: totalChunks, filename: file.name }),
    });

    const result = await completeRes.json();
    if (!completeRes.ok) throw new Error(result.error ?? 'Assembly failed');

    return result.filename; // bare temp/ basename, same convention as initPostMediaPond's syncState()
}

// GROUP — single-image colorway slot (front/back). el is a bare hidden
// <input type="file"> (dropzone:false, click-to-browse via the wrapping
// label/button in the blade). config.onStart/.onProgress/.onComplete/.onError
// mirror media-uppy.js's content-pond callbacks so the Livewire component can
// entangle state through plain Alpine assignment rather than window events —
// there's only ever one in-flight upload per slot here, no cross-pond event
// filtering needed.
window.initColorImagePond = function (el, config = {}) {
    const {
        onStart = null,
        onProgress = null,
        onComplete = null,
        onError = null,
    } = config;

    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    const uppy = new Uppy({
        autoProceed: false,
        restrictions: { maxNumberOfFiles: 1 },
        onBeforeFileAdded: (file) => {
            if (file.data.size > MAX_SIZE) {
                alert(`Image is too large — colorway assets are limited to ${Math.round(MAX_SIZE / (1024 * 1024))}MB.`);
                return false;
            }
            return true;
        },
    });

    uppy.on('file-added', async (file) => {
        const data = await prepareImage(file.data);

        if (onStart) onStart();

        try {
            const filename = await uploadChunked(data, { csrf, onProgress });
            if (onComplete) onComplete(filename);
        } catch {
            if (onError) onError();
        } finally {
            uppy.removeFile(file.id);
        }
    });

    if (el.tagName === 'INPUT') {
        el.addEventListener('change', (e) => {
            const picked = e.target.files[0];
            if (picked) {
                try { uppy.addFile(picked); } catch { /* onBeforeFileAdded already alerted */ }
            }
            e.target.value = '';
        });
    }

    return {
        browse() { el.click(); },
    };
};
