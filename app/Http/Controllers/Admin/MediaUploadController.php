<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaUploadController
{
    public function chunk(Request $request)
    {
        $uploadId = $request->upload_id;
        $index = $request->chunk_index;

        $dir = "uploads/tmp/{$uploadId}";
        Storage::disk('public')->makeDirectory($dir);

        Storage::disk('public')->putFileAs(
            $dir,
            $request->file('file'),
            $index
        );

        return response()->json(['ok' => true]);
    }

    public function complete(Request $request)
    {
        $uploadId = $request->upload_id;

        $dir = "uploads/tmp/{$uploadId}";
        $finalName = Str::uuid() . '.jpg';
        $finalPath = "uploads/final/{$finalName}";

        Storage::disk('public')->makeDirectory('uploads/final');

        $output = fopen(Storage::disk('public')->path($finalPath), 'wb');

        for ($i = 0; $i < $request->total_chunks; $i++) {

            $chunk = Storage::disk('public')->path("{$dir}/{$i}");

            if (!file_exists($chunk)) continue;

            $input = fopen($chunk, 'rb');
            stream_copy_to_stream($input, $output);
            fclose($input);
        }

        fclose($output);

        Storage::disk('public')->deleteDirectory($dir);

        return response()->json([
            'filename' => $finalName
        ]);
    }

    public function revert(Request $request)
    {
        $file = $request->getContent();

        if ($file) {
            Storage::disk('public')->delete("uploads/final/{$file}");
        }

        return response()->noContent();
    }
}









/***
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChunkUploadController extends Controller
{
    public function chunk(Request $request)
    {
        $uploadId = $request->input('upload_id');
        $index = $request->input('chunk_index');
        $path = "temp/chunks/{$uploadId}";

        Storage::disk('public')->putFileAs(
            $path,
            $request->file('file'),
            "part_{$index}"
        );

        return response()->json(['success' => true]);
    }

    public function complete(Request $request)
    {
        set_time_limit(600);

        $uploadId = $request->input('upload_id');
        $totalChunks = $request->input('total_chunks');
        $filename = str_replace(' ', '_', $request->input('filename'));

        $chunkDir = "temp/chunks/{$uploadId}";
        $finalName = uniqid() . '_' . $filename;
        $finalRelativePath = "merch/gallery/{$finalName}";

        if (!Storage::disk('public')->exists('merch/gallery')) {
            Storage::disk('public')->makeDirectory('merch/gallery');
        }

        $finalFullPath = Storage::disk('public')->path($finalRelativePath);
        $outputStream = fopen($finalFullPath, 'ab');

        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkPath = Storage::disk('public')->path("{$chunkDir}/part_{$i}");

            if (!file_exists($chunkPath)) {
                fclose($outputStream);
                throw new \Exception("Missing chunk {$i}");
            }

            $inputStream = fopen($chunkPath, 'rb');
            stream_copy_to_stream($inputStream, $outputStream);
            fclose($inputStream);
        }

        fclose($outputStream);
        Storage::disk('public')->deleteDirectory($chunkDir);

        return response()->json([
            'path' => $finalRelativePath
        ]);
    }
}













