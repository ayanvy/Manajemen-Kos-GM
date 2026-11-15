<?php

namespace App\Services;

use Cloudinary\Cloudinary;

class CloudinaryService
{
    protected Cloudinary $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary(
            config('services.cloudinary.url')
        );
    }

    public function uploadImage(
        string $filePath,
        string $folder,
        string $type = 'upload'
    ): array {
        $result = $this->cloudinary->uploadApi()->upload($filePath, [
            'folder' => $folder,
            'type' => $type,
        ]);

        return $result->getArrayCopy();
    }

    public function deleteImage(
        string $publicId,
        string $type = 'upload'
    ): array {
        $result = $this->cloudinary->uploadApi()->destroy($publicId, [
            'type' => $type,
        ]);

        return $result->getArrayCopy();
    }

    public function privateImageUrl(
        string $publicId,
        string $format,
        int $expiresAt,
        bool $attachment = false,
        ?string $targetFilename = null
    ): string {
        $options = [
            'type' => 'authenticated',
            'expires_at' => $expiresAt,
            'attachment' => $attachment,
        ];

        if ($targetFilename !== null) {
            $options['target_filename'] = $targetFilename;
        }

        return $this->cloudinary->uploadApi()->privateDownloadUrl(
            $publicId,
            $format,
            $options
        );
    }
}