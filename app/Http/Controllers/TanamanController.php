<?php

namespace App\Http\Controllers;

use App\Models\Penanaman;
use App\Models\Plant;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
namespace App\Http\Controllers;

use App\Models\Penanaman;
use App\Models\Plant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TanamanController extends Controller
{
    public function get_latest_plant(Request $request)
    {
        try {
            // Validate the request data
            $data = $request->validate([
                'id_penanaman' => 'required',
            ]);

            $id_penanaman = $data['id_penanaman'];
            $jenis_tanaman = $request->query('tanaman');

            // Query the Penanaman model
            $query = Penanaman::query();

            if ($id_penanaman) {
                $query->where('id', $id_penanaman);
            }

            if ($jenis_tanaman) {
                if ($jenis_tanaman == 'bawang_merah') {
                    $query->where('jenis_tanaman', 'bawang_merah');
                }
            }

            $penanaman = $query->first();

            // Check if penanaman record is found
            if (!$penanaman) {
                return response()->json([
                    'success' => false,
                    'message' => 'Penanaman not found.'
                ], 404);
            }

            $id_device = $penanaman->id_device;

            // Check if id_device is present
            if (!empty($id_device)) {
                $plantTypes = Plant::where('id_device', $id_device)
                    ->distinct()
                    ->pluck('id');

                $latestPlants = [];

                // Fetch the latest entry for each distinct plant type
                foreach ($plantTypes as $plantType) {
                    $latestPlant = Plant::where('id_device', $id_device)
                        ->where('id', $plantType)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if ($latestPlant) {
                        $latestPlants[$plantType] = $latestPlant;
                    }
                }

                // Check if any tanaman is found
                if (empty($latestPlants)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak ada tanaman yang ditemukan untuk id_device ini.'
                    ], 404);
                }

                // Return the found tanaman data
                return response()->json([
                    'success' => true,
                    'data' => $latestPlants
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter id_device diperlukan.'
                ], 400);
            }
        } catch (\Exception $e) {
            // Log the error message
            Log::error('Error fetching plant data: ' . $e->getMessage());

            // Return a server error response
            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function get_plant(Request $request)
    {
        try {
            // Validate the request data
            $data = $request->validate([
                'id_penanaman' => 'required',
                'date' => 'required|date_format:Y-m-d',
            ]);

            $id_penanaman = $data['id_penanaman'];
            $date = $data['date'];
            $jenis_tanaman = $request->query('tanaman');

            // Query the Penanaman model
            $query = Penanaman::query();

            if ($id_penanaman) {
                $query->where('id', $id_penanaman);
            }

            if ($jenis_tanaman) {
                if ($jenis_tanaman == 'bawang_merah') {
                    $query->where('jenis_tanaman', 'bawang_merah');
                }
            }

            $penanaman = $query->first();

            // Check if penanaman record is found
            if (!$penanaman) {
                return response()->json([
                    'success' => false,
                    'message' => 'Penanaman not found.'
                ], 404);
            }

            $id_device = $penanaman->id_device;
            $query->where('id_device', $id_device);
            $datas = Plant::where('id_device', $id_device)
                ->whereDate('created_at', $date)
                ->get();

            if ($datas->isNotEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => $datas
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Plant not found.'
                ], 404);
            }

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $th->getMessage()
            ], 500);
        }
    }

}
