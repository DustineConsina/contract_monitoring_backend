<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;

class CloudinaryService
{
    private $cloudinary;

    public function __construct()
    {
        try {
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
            
            \Log::info('CloudinaryService initialized successfully');
        } catch (\Exception $e) {
            \Log::error('Failed to initialize CloudinaryService', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Still create a Cloudinary instance even if initialization fails
            // This prevents the entire app from crashing
            $this->cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => '',
                    'api_key' => '',
                    'api_secret' => '',
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
     * @return string|null
     */
    public function generateUrl(string $public_id, int $width = 200, int $height = 200): ?string
    {
        try {
            // Get cloud name from configuration
            $cloudName = $this->cloudinary->getConfiguration()->get('cloud.cloud_name');
            
            if (!$cloudName) {
                \Log::warning('Cloud name not configured for Cloudinary');
                return null;
            }

            // Build URL with transformations in correct format (comma-separated)
            // Format: https://res.cloudinary.com/{cloud_name}/image/upload/{transformations}/{public_id}
            $transformations = "f_auto,q_auto,w_{$width},h_{$height},c_fill";
            $url = "https://res.cloudinary.com/{$cloudName}/image/upload/{$transformations}/{$public_id}";
            
            return $url;
        } catch (\Exception $e) {
            \Log::warning('Failed to generate Cloudinary URL', [
                'public_id' => $public_id,
                'error' => $e->getMessage()
            ]);
            // Return null if unable to generate URL
            return null;
        }
    }
}
