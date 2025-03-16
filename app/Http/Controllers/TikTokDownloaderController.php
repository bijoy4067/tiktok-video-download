<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class TikTokDownloaderController extends Controller
{
    public function index()
    {
        return view('tiktok.index');
    }

    public function getFile(Request $request)
    {
        $request->validate([
            'filename' => 'required|string'
        ]);

        $filePath = storage_path('app/public/downloads/' . $request->filename);

        if (!file_exists($filePath)) {
            return back()->with('error', 'File not found');
        }

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function download(Request $request)
    {
        $request->validate([
            'url' => 'required|url'
        ]);

        try {
            $scriptPath = base_path('python/tiktok.sh');

            // Create downloads directory in public storage
            $downloadPath = storage_path('app/public/downloads');
            if (!file_exists($downloadPath)) {
                mkdir($downloadPath, 0755, true);
            }

            // Extract filename components from URL
            $url_parts = explode('/', $request->url);
            $user = $url_parts[3] ?? '';
            $id = $url_parts[5] ?? '';
            $filename = "{$user}-{$id}.mp4";

            // Sanitize and escape the URL
            $escapedUrl = escapeshellarg($request->url);

            // Execute bash script using exec with escaped URL
            $command = sprintf(
                "cd %s && bash %s %s 2>&1",
                escapeshellarg($downloadPath),
                escapeshellarg($scriptPath),
                $escapedUrl
            );
            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);

            // Log the output for debugging
            \Log::info('Command Output:', $output);

            $filePath = '/storage/downloads/' . $filename;
            $publicUrl = asset('storage/downloads/' . $filename);

            if (!file_exists($filePath)) {
                \Log::error('File not found after download attempt');
                return back()->with('error', 'Video download failed. Please try again.');
            }

            // Return success message with filename and public URL
            return back()
                ->with('success', 'Video processed successfully! Click the button below to download.')
                ->with('filename', $filename)
                ->with('download_url', $publicUrl);
        } catch (\Exception $e) {
            \Log::error('Download Error: ' . $e->getMessage());
            return back()->with('error', 'An error occurred during download. Please try again.');
        }
    }
}
