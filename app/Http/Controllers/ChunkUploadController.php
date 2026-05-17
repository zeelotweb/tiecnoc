<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Storage, Log};
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class ChunkUploadController extends Controller
{
    protected string $disk = 'public';
    protected int $maxSize = 200 * 1024 * 1024; // 200MB limit

    /**
     * Stage 1: Receive 5MB Chunks.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'upload_id'   => 'required|uuid',
            'chunk_index' => 'required|integer|min:0',
            'file'        => 'required|file|max:10240', // Max 10MB per chunk safety
        ]);

        $userId = Auth::id();
        abort_unless($userId, 401);

        $dir = "temp/chunks/{$userId}/{$request->upload_id}";
        
        // Store with index as name. putFileAs is more secure than move()
        Storage::disk($this->disk)->putFileAs($dir, $request->file('file'), $request->chunk_index);

        return response()->json(['status' => 'chunk_received']);
    }

    /**
     * Stage 2: Finalize & Merge.
     */
    public function complete(Request $request)
    {
        $request->validate([
            'upload_id'    => 'required|uuid',
            'total_chunks' => 'required|integer|min:1',
            'filename'     => 'required|string',
        ]);

        $userId = Auth::id();
        $chunkDir = "temp/chunks/{$userId}/{$request->upload_id}";

        if (!Storage::disk($this->disk)->exists($chunkDir)) {
            return response()->json(['error' => 'Upload directory not found.'], 404);
        }

        // Prepare Final Destination
        $extension = strtolower(pathinfo($request->filename, PATHINFO_EXTENSION));
        $finalName = (string) Str::uuid() . ".{$extension}";
        $finalPath = "temp/{$finalName}";
        
        $absoluteFinal = Storage::disk($this->disk)->path($finalPath);

        // Professional Stream Processing
        $output = fopen($absoluteFinal, 'ab');
        
        try {
            for ($i = 0; $i < $request->total_chunks; $i++) {
                $chunkPath = "{$chunkDir}/{$i}";
                
                if (!Storage::disk($this->disk)->exists($chunkPath)) {
                    throw new \Exception("Missing chunk {$i}");
                }

                // Stream the chunk instead of loading it all into memory
                $chunkStream = Storage::disk($this->disk)->readStream($chunkPath);
                stream_copy_to_stream($chunkStream, $output);
                fclose($chunkStream);
            }
        } catch (\Exception $e) {
            fclose($output);
            Storage::disk($this->disk)->delete($finalPath);
            return response()->json(['error' => $e->getMessage()], 422);
        }

        fclose($output);

        // Post-Merge Cleanup & Validation
        Storage::disk($this->disk)->deleteDirectory($chunkDir);
        
        $size = Storage::disk($this->disk)->size($finalPath);
        if ($size > $this->maxSize) {
            Storage::disk($this->disk)->delete($finalPath);
            return response()->json(['error' => 'File too large'], 422);
        }

        return response()->json([
            'filename' => $finalName, // This is the serverId returned to FilePond
            'mime'     => Storage::disk($this->disk)->mimeType($finalPath),
            'size'     => $size,
        ]);
    }

    public function revert(Request $request)
    {
        $filename = $request->getContent() ?: $request->input('filename');
        if ($filename) {
            Storage::disk($this->disk)->delete("temp/{$filename}");
        }
        return response()->json(['status' => 'reverted']);
    }
}

