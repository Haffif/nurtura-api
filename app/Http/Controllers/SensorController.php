<?php

namespace App\Http\Controllers;

use App\Models\Penanaman;
use Illuminate\Support\Facades\Log;

use App\Models\Sensor;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\Request;

class SensorController extends Controller
{
    public function get_sensor(Request $request)
    {
        try {
            $data = $request->validate([
                'id_user' => 'required_without_all:id_device,id_penanaman',
                'id_device' => 'required_without_all:id_user,id_penanaman',
                'id_penanaman' => 'required_without_all:id_user,id_device'
            ]);

            $type_sensor = $request->query('type_sensor');
            $id_device = $request->query('id_device');
            $id_penanaman = $request->query('id_penanaman');

            // Retrieve id_device using id_penanaman if provided and id_device is not
            if (!$id_device && $id_penanaman) {
                $penanaman = Penanaman::where('id', $id_penanaman)->first();

                if (!$penanaman) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak ditemukan penanaman untuk ID ini.'
                    ], 404);
                }

                $id_device = $penanaman->id_device; // Use the device id from Penanaman
            }

            // Retrieve id_device using id_user if id_user is provided and id_device is not
            if (!$id_device && $data['id_user']) {
                $user = UserDevice::where('id_user', $data['id_user'])->first();

                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak ditemukan device untuk user ini. Mohon daftarkan device dahulu!'
                    ], 404);
                }

                $id_device = $user->id_device; // Use the device id from user device
            }

            // Proceed if id_device is available
            if ($id_device) {
                $data_sensor = Sensor::where('id_device', $id_device)->get();
                if ($data_sensor->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak ada data sensor yang ditemukan untuk id_device ini.'
                    ], 404);
                }

                // Filter sensor data by type if type_sensor is specified
                if ($type_sensor) {
                    $filtered_data = $data_sensor->filter(function ($sensor) use ($type_sensor) {
                        return isset($sensor[$type_sensor]);
                    })->map(function ($sensor) use ($type_sensor, $id_device) {
                        return [
                            'id_device' => $id_device,
                            $type_sensor => $sensor[$type_sensor],
                            'timestamp_pengukuran' => $sensor['timestamp_pengukuran'],
                            'created_at' => $sensor['created_at'],
                            'updated_at' => $sensor['updated_at'],
                        ];
                    });

                    return response()->json([
                        'success' => true,
                        'data' => $filtered_data
                    ], 200);
                };

                return response()->json([
                    'success' => true,
                    'data' => $data_sensor
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter id_device diperlukan.'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error', 'message' => $e->getMessage()], 500);
        }
    }


    public function get_latest_sensor(Request $request)
    {
        try {
            $id_user = $request->query('id_user');
            $user = UserDevice::where('id_user', $id_user)->first();
            if ($user == null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ditemukan device untuk user ini. Mohon daftarkan device dahulu !'
                ], 404);
            };

            $id_device = $user->id_device;

            if (!empty($id_device)) {
                // $data_sensor = Sensor::where('id_device', $id_device)
                //     ->orderBy('created_at', 'desc') // Assuming 'created_at' is the timestamp column
                //     ->first();
                $plantIds = ['plant001', 'plant002', 'plant003'];
                $data_sensor = Sensor::whereIn('id_plant', $plantIds)
                    ->select('id_plant', 'id', 'timestamp_pengukuran', 'suhu', 'kelembapan_udara', 'kelembapan_tanah', 'ph_tanah', 'nitrogen', 'fosfor', 'kalium')
                    ->orderBy('timestamp_pengukuran', 'desc')
                    ->get()
                    ->unique('id_plant');

                if ($data_sensor == null) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak ada data_sensor yang ditemukan untuk id_device ini.'
                    ], 404);
                }

                $total_soil = 0;
                $total_hum = 0;
                $total_temp = 0;
                $total_ph = 0;
                $total_nitrogen = 0;
                $total_fosfor = 0;
                $total_kalium = 0;
                $size = count($data_sensor);

                foreach ($data_sensor as $data) {
                    $total_soil += $data->kelembapan_tanah;
                    $total_hum += $data->kelembapan_udara;
                    $total_temp += $data->suhu;
                    $total_ph += $data->ph_tanah;
                    $total_nitrogen += $data->nitrogen;
                    $total_fosfor += $data->fosfor;
                    $total_kalium += $data->kalium;
                }

                $avgData = [
                    "kelembapan_tanah" => $total_soil / $size,
                    "kelembapan_udara" => $total_hum / $size,
                    "suhu" => $total_temp / $size,
                    "ph_tanah" => $total_ph / $size,
                    "nitrogen" => $total_nitrogen / $size,
                    "fosfor" => $total_fosfor / $size,
                    "kalium" => $total_kalium / $size,
                ];

                return response()->json([
                    'success' => true,
                    'data' => $avgData
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter id_device diperlukan.'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error', 'message' => $e->getMessage()], 500);
        }
    }
}
