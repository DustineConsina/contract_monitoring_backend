<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;

class CloudinaryService
{
    private $cloudinary;

    public function __construct()
    {
        // Parse CLOUDINARY_URL if set
        $cloudinaryUrl = env('CLOUDINARY_URL');
        
        if ($cloudinaryUrl) {
            // Parse cloudinary://key:secret@cloudname format
            $this->cloudinary = new Cloudinary($cloudinaryUrl);
        } else {
            // Fallback to individual env variables
            $this->cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => env('CLOUDINARY_CLOUD_NAME', ''),
                    'api_key' => env('CLOUDINARY_API_KEY', ''),
                    'api_secret' => env('CLOUDINARY_API_SECRET', ''),
                ]
            ]);
        }
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

            $result = $this->cloudinary->uploadApi()->upload(
                $file->getRealPath(),
                $options
            );

            \Log::info('Cloudinary upload successful', [
                'public_id' => $result['public_id'],
                'url' => $result['secure_url']
            ]);

            return [
                'success' => true,
                'url' => $result['secure_url'],
                'public_id' => $result['public_id'],
                'resource_type' => $result['resource_type']
            ];
        } catch (\Exception $e) {
            \Log::error('Cloudinary upload error', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
                'trace' => $e->getTraceAsString()
            ]);
            
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
            $this->cloudinary->uploadApi()->destroy($public_id);
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
        try {
            // Simple URL generation with transformations
            $url = $this->cloudinary->image($public_id)
                ->format('auto')
                ->quality('auto')
                ->toUrl();
            
            return $url;
        } catch (\Exception $e) {
            \Log::warning('Failed to generate Cloudinary URL', [
                'public_id' => $public_id,
                'error' => $e->getMessage()
            ]);
            // Return a basic Cloudinary URL if transformation builder fails
            $cloudName = $this->cloudinary->getConfiguration()->get('cloud.cloud_name');
            return "https://res.cloudinary.com/{$cloudName}/image/upload/q_auto,f_auto/{$public_id}";
        }
    }
}
