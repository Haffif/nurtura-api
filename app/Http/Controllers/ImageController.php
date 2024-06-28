<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Http;
use ImageKit\ImageKit;
use Exception;

class ImageController extends Controller
{
    public function uploadImage(Request $request)
    {
        try {
            // Initialize ImageKit instance
            $imageKit = new ImageKit(
                "public_9UIFfVqH9SPtUKR/d4aUH41v24o=",
                "private_CYLQkYLbA7cga3APFYg6jbGIIn8=",
                "https://ik.imagekit.io/nurtura-grow"
            );

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = $file->getClientOriginalName();
                $fileData = fopen($file->getPathName(), 'r');
                Log::info('Received and combined base64 images:', [
                    'img' => $file,
                ]);
                $uploadOptions = [
                    'file' => $fileData, // Required
                    'fileName' => $fileName, // Required
                    'useUniqueFileName' => true, // Optional
                    'folder' => '/UploadedImages', // Optional
                ];

                // Upload the file to ImageKit
                $uploadResponse = $imageKit->upload($uploadOptions);

                $imageUrl = $uploadResponse->result->url;

                // Post the image URL to the external prediction API
                $predictResponse = Http::attach(
                    'file',
                    file_get_contents($file->getPathName()),
                    $fileName
                )->post('https://genial-union-415103.et.r.appspot.com/predict/');

                return response()->json([
                    'success' => true,
                    'message' => 'Image uploaded and prediction request sent successfully',
                    'image_url' => $imageUrl,
                    'predict_response' => $predictResponse->body()
                ]);
            } else {
                return response()->json(['success' => false, 'message' => 'No valid image found or image upload failed.']);
            }
        } catch (Exception $e) {
            // Catch any exceptions that occur during the upload process
            return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
    }
}
