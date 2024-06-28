<?php

namespace App\Http\Controllers;

use App\Models\Fertilizer;
use App\Models\SopPemupukan;
use App\Models\UserDevice;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PemupukanController extends Controller
{
    public function get_data(Request $request)
    {
        try {
            $data = $request->validate([
                'id_user' => 'required',
            ]);

            $id_user = $data['id_user'];
            $id_device = UserDevice::where('id_user', $id_user)->first();

            if (!empty($id_user)) {
                // Mencari semua irrigation yang memiliki id_user yang sesuai
                $irrigation = Fertilizer::where('id_user', $id_user)->get();

                // Memeriksa apakah hasilnya kosong
                if ($irrigation->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak ada data yang ditemukan untuk id_user ini.'
                    ], 404);
                }

                return response()->json([
                    'success' => true,
                    'data' => $irrigation
                ], 200);
            }
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Menangani kesairrigation server
            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function get_sop(Request $request)
    {
        try {
            $id_penanaman = $request->query('id_penanaman');

            if ($id_penanaman) {
                $data = SopPemupukan::where('id_penanaman', $id_penanaman)->first();
                if (!empty($data)) {
                    return response()->json([
                        'success' => true,
                        'data' => $data
                    ], 200);
                }
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data yang ditemukan untuk penanaman ini.'
                ], 404);
            }
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function input_sop(Request $request)
    {
        try {
            $data = $request->validate([
                'id_penanaman' => 'required',
                'hst' => 'required',
                'min' => 'required',
                'max' => 'required',
            ]);

            $sop = SopPemupukan::updateOrCreate(
                ['id_penanaman' => $data['id_penanaman']], // Key to find                
                [
                    'hari_setelah_tanam' => $data['hst'],
                    'tinggi_tanaman_minimal_mm' => $data['min'],
                    'tinggi_tanaman_maksimal_mm' => $data['max']
                ]
            );

            return response()->json([
                'success' => true,
                'message' => $sop->wasRecentlyCreated ? 'Data sop pemupukan berhasil ditambahkan!' : 'Data sop pemupukan berhasil diperbarui!',
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error', 'message' => $e->getMessage()], 500);
        }
    }
}
