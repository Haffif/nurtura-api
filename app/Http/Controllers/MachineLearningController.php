<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

use App\Models\Sensor;
use App\Models\Penanaman;

class MachineLearningController extends Controller
{
    private $URL_IRRIGATION;
    private $URL_FERTILIZER;
    private $URL_PREDICT;

    public function __construct()
    {
        $this->URL_IRRIGATION = config('services.sensor_model.url') . '/penyiraman';
        $this->URL_FERTILIZER = config('services.sensor_model.url') . '/pemupukan';
        $this->URL_PREDICT = config('services.sensor_model.url') . '/predict';
    }
    public function irrigation(Request $request)
    {
        // Define validation rules
        $rules = [
            'SoilMoisture' => 'required|numeric',
            'Humidity' => 'required|numeric',
            'temperature' => 'required|numeric',
        ];

        // Define validation messages
        $messages = [
            'SoilMoisture.required' => 'SoilMoisture dibutuhkan!',
            'Humidity.required' => 'Humidity dibutuhkan!',
            'temperature.required' => 'temperature dibutuhkan!',
            'SoilMoisture.numeric' => 'SoilMoisture harus berupa angka!',
            'Humidity.numeric' => 'Humidity harus berupa angka!',
            'temperature.numeric' => 'temperature harus berupa angka!',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $rules, $messages);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                "status" => 400,
                "message" => "Need SoilMoisture, Humidity, and temperature",
                "errors" => $validator->errors(),
            ], 400);
        }

        // Validation passed, use the validated data
        $validatedData = $validator->validated();

        try {
            // Attempt to make the HTTP request
            $response = Http::post($this->URL_IRRIGATION, $validatedData);

            // Check response status
            if ($response->successful()) {
                return response()->json([
                    "status" => $response->status(),
                    "data" => $response->json(),
                ]);
            } else {
                // Handle the error
                return response()->json([
                    "status" => $response->status(),
                    "message" => "Terjadi kesalahan: " . $response->body(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            // Catch any exceptions and return an error
            return response()->json([
                "status" => 500,
                "message" => "Terjadi kesalahan: " . $e->getMessage(),
            ], 500);
        }
    }

    public function fertilizer(Request $request)
    {
        // Validation Rules
        $rules = [
            'tinggi_tanaman' => 'required|numeric',
            'hst' => 'required|numeric',
        ];

        // Validation Messages
        $messages = [
            'tinggi_tanaman.required' => 'Tinggi tanaman dibutuhkan!',
            'hst.required' => 'HST dibutuhkan!',
            'tinggi_tanaman.numeric' => 'Tinggi tanaman harus berupa angka!',
            'hst.numeric' => 'HST harus berupa angka!',
        ];

        // Validate Request Data
        $validator = Validator::make($request->all(), $rules, $messages);
        $validatedData = $validator->validated();

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json([
                "status" => 400,
                "message" => "Need tinggi_tanaman and hst",
                "errors" => $validator->errors(),
            ], 400);
        }

        try {
            // Attempt to make the HTTP request
            $response = Http::post($this->URL_FERTILIZER, $validatedData);

            // Check response status
            if ($response->successful()) {
                return response()->json([
                    "status" => $response->status(),
                    "data" => $response->json(),
                ]);
            } else {
                // Handle the error appropriately
                return response()->json([
                    "status" => $response->status(),
                    "message" => "Terjadi kesalahan: " . $response->body(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            // Catch any exceptions and return an error
            return response()->json([
                "status" => 500,
                "message" => "Terjadi kesalahan: " . $e->getMessage(),
            ], 500);
        }
    }

    public function predict(Request $request)
    {
        // Get prediction result from ML service
        $response_predict = Http::get($this->URL_PREDICT)->json();

        // Save prediction result to database
        // Sensor::create([
        //     'id_penanaman' => Penanaman::where('alat_terpasang', true)->first()->id_penanaman,
        //     'suhu' => $response_predict['temperature'],
        //     'kelembapan_udara' => $response_predict['Humidity'],
        //     'kelembapan_tanah' => $response_predict['SoilMoisture'],
        //     'timestamp_prediksi_sensor' => $response_predict['Time'],
        //     'created_at' => Carbon::now(),
        // ]);

        // Run irrigation to get the irrigation command
        $url_irrigation = route('ml.irrigation');
        $response_irrigation = Http::post($url_irrigation, $response_predict)->json();

        // Return the response with prediction and irrigation data
        return response()->json([
            'status' => 200,
            'data' => [
                'predict' => $response_predict,
                'irrigation' => $response_irrigation,
            ],
        ]);
    }

}
