<?php

namespace App\Http\Controllers\Antares;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ControllerDevice
{
    public static function handleController(Request $request)
    {
        $rules = [
            'data' => 'required',
        ];

        $messages = [
            'data.required' => 'Data dibutuhkan!',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json([
                "status" => 400,
                "message" => "Need data",
                "errors" => $validator->errors(),
            ], 400);
        }

        $URL = config('services.antares.url') . config('services.antares.controller');
        $access_key = config('services.antares.access_key');

        $headers = [
            'X-M2M-Origin' => $access_key,
            'Content-Type' => 'application/json;ty=4;',
            'Accept' => 'application/json',
        ];

        $body = [
            'm2m:cin' => [
                "xmlns:m2m" => "http://www.onem2m.org/xml/protocols",
                "cnf" => "application/json",
                'con' => json_encode([
                    "data" => $request->input('data'),
                    "durasi" => $request->input('durasi'),
                ]),
            ],
        ];

        try {
            $response = Http::withHeaders($headers)->post($URL, $body);

            if ($response->successful()) {
                return response()->json([
                    "status" => $response->status(),
                    "data" => $response->json(),
                ]);
            } else {
                return response()->json([
                    "status" => $response->status(),
                    "message" => "Terjadi kesalahan: " . $response->body(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Terjadi kesalahan: " . $e->getMessage(),
            ], 500);
        }
    }
}
