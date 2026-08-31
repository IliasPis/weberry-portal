<?php

namespace App\Services;

use App\Models\File as FileModel;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Every upload in the app (ticket attachments now, documents/invoices in a
 * later stage) goes through here so storage and validation stay in one
 * place. Files live on the private 'local' disk - never 'public' - and are
 * only ever served back out through a Policy-gated download route.
 */
class FileUploadService
{
    private const DISK = 'local';

    public function store(UploadedFile $file, User $uploader, string $directory = 'uploads'): FileModel
    {
        $key = $file->storeAs(
            $directory,
            Str::uuid()->toString().'.'.$file->getClientOriginalExtension(),
            self::DISK,
        );

        return FileModel::create([
            'storage_disk' => self::DISK,
            'storage_key' => $key,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'uploaded_by_user_id' => $uploader->id,
        ]);
    }

    public function delete(FileModel $file): void
    {
        Storage::disk($file->storage_disk)->delete($file->storage_key);
        $file->delete();
    }
}
