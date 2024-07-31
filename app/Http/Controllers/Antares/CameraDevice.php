<?php

namespace App\Http\Controllers\Antares;

use Carbon\Carbon;

use App\Models\Plant;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use ImageKit\ImageKit;
use Intervention\Image\Facades\Image;

class CameraDevice
{
    public static function handleCamera(Request $request)
    {
        try {
            // Direktori untuk menyimpan file .txt
            $txtDir = storage_path('newBase64');
            if (!file_exists($txtDir)) {
                mkdir($txtDir, 0777, true);
            }

            // Buka atau buat file .txt
            $txtFile = $txtDir . '/base64_images.txt';

            // Inisialisasi string untuk menyimpan data base64
            $all_base64_images = '';

            // Jika file .txt sudah ada, baca kontennya
            if (file_exists($txtFile)) {
                $all_base64_images = file_get_contents($txtFile);
            }
            // Ambil base64 dari request
            $con = $request->input('m2m:sgn.m2m:nev.m2m:rep.m2m:cin.con');
            $string = $request->input('m2m:sgn.m2m:nev.m2m:rep.m2m:cin.pi');
            $parts = explode("/", $string);
            $con_data = json_decode($con, true);

            if (array_key_exists('image_data', $con_data)) {
                $all_base64_images .= "data:image/jpeg;base64," . $con_data['image_data'];

                if ($con_data['status'] === "end") {

                    Log::info($con_data['status']);

                    // Encode base64 menjadi gambar
                    $decoded_image = base64_decode(str_replace("data:image/jpeg;base64,", "", $all_base64_images));

                    // Kirim gambar ke ImageKit
                    $imageKit = new ImageKit(
                        config('services.imagekit.public'),
                        config('services.imagekit.private'),
                        config('services.imagekit.url')
                    );

                    // Kirim gambar ke ImageKit
                    $uploadResponse = $imageKit->upload(array(
                        "file" => base64_encode($decoded_image),
                        "fileName" => "bawang.jpg", // Ganti dengan nama file yang sesuai
                        "useUniqueFileName" => true
                    ));

                    $imageUrl = $uploadResponse->result->url;
                    
                    Log::info('Imagekit:', [
                        'imgkit' => $imageUrl,
                    ]);

                    $image = Image::make($imageUrl);
                    $image->encode('jpg', 100);

                    $predictResponse = Http::attach(
                        'file',
                        $image,
                        'image.jpg'
                    )->post(config('services.img_model.url') . '/predict/');

                    Log::info($predictResponse->body());
                    $data = json_decode($predictResponse->body());

                    // Setelah selesai mengirim gambar, hapus file .txt
                    unlink($txtFile);

                    $plant = Plant::create(
                        // ['id' => $con_data['id']],
                        [
                            'id' => $con_data['id'],
                            'id_device' => 'CAEP0v54HFOtV1FsuyB',
                            'url' => $imageUrl,
                            'posisi' => $con_data['posisi'],
                            'detection' => $data->class,
                            'accuration' => $data->confidence,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]
                    );

                    // Optional: Add a custom timestamp field for measurement time, not covered by default timestamps
                    $plant->save();

                    return response()->json([
                        "status" => 200,
                        "message" => "All images uploaded and processed successfully.",
                    ], 200);
                }

                // Simpan string base64 ke dalam file .txt
                file_put_contents($txtFile, $all_base64_images);

                return response()->json([
                    "status" => 200,
                    "message" => "Base64 images received and saved successfully.",
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                "status" => 500,
                "message" => "An error occurred: " . $e->getMessage(),
            ], 500);
        }
    }
}
