<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Http\UploadedFile;

class CloudinaryService
{
    private $cloudinary;
    private $uploadApi;

    public function __construct()
    {
        // Cloudinary SDK automatically reads CLOUDINARY_URL environment variable
        // Format: cloudinary://api_key:api_secret@cloud_name
        $this->cloudinary = new Cloudinary();

        $this->uploadApi = new UploadApi($this->cloudinary);
    }

    /**
     * Upload a file to Cloudinary
     *
     * @param UploadedFile $file
     * @param string $folder
     * @param string $public_id
     * @return array
     */
    public function uploadFile(UploadedFile $file, string $folder = 'profile-pictures', ?string $public_id = null): array
    {
        try {
            $options = [
                'folder' => $folder,
                'resource_type' => 'auto',
                'quality' => 'auto',
                'fetch_format' => 'auto',
            ];

            if ($public_id) {
                $options['public_id'] = $public_id;
            }

            $result = $this->uploadApi->upload(
                $file->getRealPath(),
                $options
            );

            return [
                'success' => true,
                'url' => $result['secure_url'],
                'public_id' => $result['public_id'],
                'resource_type' => $result['resource_type']
            ];
        } catch (\Exception $e) {
            \Log::error('Cloudinary upload error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete a file from Cloudinary
     *
     * @param string $public_id
     * @return boolean
     */
    public function deleteFile(string $public_id): bool
    {
        try {
            $this->uploadApi->destroy($public_id);
            return true;
        } catch (\Exception $e) {
            \Log::error('Cloudinary delete error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate an optimized URL for an image
     *
     * @param string $public_id
     * @param int $width
     * @param int $height
     * @return string
     */
    public function generateUrl(string $public_id, int $width = 200, int $height = 200): string
    {
        return $this->cloudinary->image($public_id)
            ->resize()
            ->width($width)
            ->height($height)
            ->crop('fill')
            ->quality('auto')
            ->format('auto')
            ->toUrl();
    }
}
