<?php

namespace App\Modules\ProfilePic;

use Illuminate\Support\Facades\Storage;

class ProfilePicService
{
    private string $path = 'ProfilePic/';

    private function cleanEmail(string $email): string
    {
        $email = strtolower(trim($email));

        $prefix = preg_replace('/[^a-z0-9]/', '', $email);
        $prefix = substr($prefix, 0, 8);

        $hash = substr(hash('sha256', $email), 0, 8);

        return $prefix . '_' . $hash;
    }

    public function upload(string $email, $file): void
    {
        $clean = $this->cleanEmail($email);
        $ext = $file->getClientOriginalExtension();
        $filename = $clean . '.' . $ext;
        
        Storage::makeDirectory($this->path);

        Storage::putFileAs($this->path, $file, $filename);
        
        // DEBUG:
    \Log::info("UPLOAD FILE:", [
        'clean' => $clean,
        'ext' => $ext,
        'filename' => $filename,
        'exists_after_upload' => Storage::exists($this->path . $filename),
        'files_in_dir' => Storage::files($this->path)
    ]);
    }

    public function download(string $email)
    {
        $clean = $this->cleanEmail($email);
    
        $files = Storage::files($this->path);
    
        $match = collect($files)->first(function ($f) use ($clean) {
            return preg_match("/{$clean}\.(jpg|jpeg|png|gif)$/i", basename($f));
        });
    
        if (!$match) {
            $match = 'ProfilePic/profile.png';
        }
    
        $absolutePath = storage_path('app/private/' . $match);
    
        if (!file_exists($absolutePath)) {
            abort(404);
        }
    
        $filename = basename($absolutePath);
        $mime = mime_content_type($absolutePath);
    
        return response()->file($absolutePath, [
            'Content-Type' => $mime,
            'X-File-Name'  => $filename
        ]);
    }


}
