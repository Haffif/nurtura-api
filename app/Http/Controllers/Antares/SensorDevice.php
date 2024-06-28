<?php

namespace App\Http\Controllers\Antares;

use Carbon\Carbon;

use App\Models\Sensor;
use App\Models\Device;
use App\Models\Irrigation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SensorDevice
{
    public static function handleSensor(Request $request)
    {
        try {
            $con = $request->input('m2m:sgn.m2m:nev.m2m:rep.m2m:cin.con');
            $string = $request->input('m2m:sgn.m2m:nev.m2m:rep.m2m:cin.pi');
            $parts = explode("/", $string);
            $id_device = end($parts);
            $con_data = json_decode($con, true);

            Self::processSensorData($con_data, $id_device);

            return response()->json([
                "status" => 200,
                "message" => "Data Received Successfully",
            ], 200);
        } catch (\Exception $e) {
            // Log::error($e->getMessage());
            return response()->json([
                "status" => 500,
                "message" => "An error occurred: " . $e->getMessage(),
            ], 500);
        }
    }

    static function processSensorData($con_data, $id_device)
    {
        $currentTimestamp = Carbon::now()->format('Y:m:d H:i');
        $isComplete = false;

        $sensorData = [
            'id_device' => 'CAEP0v54HFOtV1FsuyB',
            'timestamp_pengukuran' => $currentTimestamp,
            'id_plant' => $con_data['id_device'] == 'master' ? 0 : $con_data['id_device'],
            'suhu' => $con_data['suhu'] == '-' ? 0 : $con_data['suhu'],
            'kelembapan_udara' => $con_data['kelembapan_udara'] == '-' ? 0 : $con_data['kelembapan_udara'],
            'kelembapan_tanah' => $con_data['kelembapan_tanah'] == '-' ? 0 : $con_data['kelembapan_tanah'],
            'ph_tanah' => $con_data['ph_tanah'] == '-' ? 0 : $con_data['ph_tanah'],
            'nitrogen' => $con_data['nitrogen'] == '-' ? 0 : $con_data['nitrogen'],
            'fosfor' => $con_data['fosfor'] == '-' ? 0 : $con_data['fosfor'],
            'kalium' => $con_data['pottasium'] == '-' ? 0 : $con_data['pottasium'],
        ];

        $filteredData = array_filter($sensorData, function ($value) {
            return $value !== 0;
        });

        $sensor = Sensor::where('timestamp_pengukuran', $currentTimestamp)->where('id_device', 'CAEP0v54HFOtV1FsuyB');

        try {
            if ($sensor->exists()) {
                if (!empty($filteredData)) {
                    $sensor->update($filteredData);
                    Log::info("Sensor data updated for device 'CAEP0v54HFOtV1FsuyB'.");
                    $isComplete = true;
                    Log::info($isComplete);
                }
            } else {
                Sensor::create($sensorData);
                Log::info("New sensor data created for device 'CAEP0v54HFOtV1FsuyB'.");
            }
        } catch (\Exception $e) {
            Log::error('Failed to save sensor data for device CAEP0v54HFOtV1FsuyB: ' . $e->getMessage());
        }

        if ($isComplete) {
            $currentTimestamp = Carbon::now()->format('Y:m:d H:i');
            $datas = Sensor::where('id_device', 'CAEP0v54HFOtV1FsuyB')->where('timestamp_pengukuran', $currentTimestamp)->get();

            $total_soil = 0;
            $total_hum = 0;
            $total_temp = 0;
            $size = count($datas);

            foreach ($datas as $data) {
                $total_soil += $data->kelembapan_tanah;
                $total_hum += $data->kelembapan_udara;
                $total_temp += $data->suhu;
            }

            $avgData = [
                "SoilMoisture" => $total_soil / $size,
                "Humidity" => $total_hum / $size,
                "temperature" => $total_temp / $size
            ];

            // // Rekomendasi ML irigasi 
            $response = Http::post(route('ml.irrigation'), $avgData);
            Log::info($response);
            Log::info($avgData);

            $data_response = json_decode($response, true)['data'];

            if ($data_response['Informasi Kluster']['nyala']) {
                $type = 1;
                $status = 'OPEN';
                $durasi = $data_response['Informasi Kluster']['waktu'];
                $menit = $durasi / 60;
                $volume = 7 * $menit;

                $dataDownlink = ([
                    'data' => $type . $status . $durasi
                ]);

                $responseDownlink = Http::post(route('antares.downlink'), $dataDownlink);

                Log::info($responseDownlink);
                Log::info($responseDownlink->status());

                if ($responseDownlink->status() == 200) {
                    try {
                        $irrigation = Irrigation::create([
                            'id_device' => 'CAEP0v54HFOtV1FsuyB',
                            'rekomendasi_volume' => $volume,
                            'kondisi' => $data_response['Kondisi'],
                            'saran' => $data_response['Saran'],
                        ]);

                        $start = Carbon::now();
                        $end = $start->copy()->addSeconds($durasi);

                        $addDevice = Device::create([
                            'id_device' => 'CAEP0v54HFOtV1FsuyB',
                            'tipe_intruksi' => $type,
                            'durasi' => $durasi,
                            'start' => $start,
                            'isActive' => true,
                            'end' => $end,
                            'volume' => $volume,
                            'mode' => 'auto'
                        ]);
                    } catch (\Exception $e) {
                        // Log the error
                        Log::error('Error creating irrigation record: ' . $e->getMessage());
                        // You might also want to handle the error in a way that is appropriate for your application
                    }
                }
            } else {
                $irrigation = Irrigation::create([
                    'id_device' => 'CAEP0v54HFOtV1FsuyB',
                    'rekomendasi_volume' => 0,
                    'kondisi' => $data_response['Kondisi'],
                    'saran' => $data_response['Saran'],
                ]);
            }
        }
    }
}
