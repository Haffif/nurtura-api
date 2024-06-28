<?php

namespace App\Http\Controllers;

use App\Models\Penanaman;
use App\Models\Plant;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TanamanController extends Controller
{
    public function get_plant(Request $request)
    {
        try {
            $data = $request->validate([
                'id_penanaman' => 'required',
            ]);

            $id_penanaman = $data['id_penanaman'];
            $jenis_tanaman = $request->query('tanaman');


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

            $id_device = $penanaman->id_device;

            if (!empty($id_device)) {
                $tanaman = Plant::where('id_device', $id_device)->get();

                if ($tanaman->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak ada tanaman yang ditemukan untuk id_device ini.'
                    ], 404);
                }
                return response()->json([
                    'success' => true,
                    'data' => $tanaman
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
