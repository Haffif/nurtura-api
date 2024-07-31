<?php

namespace App\Http\Controllers;

use App\Models\Fertilizer;
use App\Models\Lahan;
use App\Models\log_tinggi;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

use App\Models\Penanaman;
use Illuminate\Support\Facades\Http;

class PenanamanController extends Controller
{
    public function get_penanaman(Request $request)
    {
        try {
            $data = $request->validate([
                'id_user' => 'required_without_all:id_penanaman',
                'id_penanaman' => 'required_without_all:id_user'
            ]);

            $id_user = $request->query('id_user');
            $id_penanaman = $request->query('id_penanaman');

            if (!empty($id_user)) {
                $penanaman = Penanaman::where('id_user', $id_user)->get();
            } else {
                $penanaman = Penanaman::where('id', $id_penanaman)->get();
            }

            if ($penanaman->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada penanaman yang ditemukan untuk id ini.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $penanaman
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error', 'message' => $e->getMessage()], 500);
        }
    }

    public function get_tinggi(Request $request)
    {
        try {
            $data = $request->validate([
                'id_user' => 'required_without_all:id_penanaman',
                'id_penanaman' => 'required_without_all:id_user'
            ]);

            $id_user = $request->query('id_user');
            $id_penanaman = $request->query('id_penanaman');

            if (!empty($id_user)) {
                $penanaman = Penanaman::where('id_user', $id_user)->get();
                $id_penanaman = $penanaman->id;

                $log_tinggi = log_tinggi::where('id_penanaman', $id_penanaman)->get();
            } else {
                $log_tinggi = log_tinggi::where('id_penanaman', $id_penanaman)->get();
            }

            if ($log_tinggi->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada log_tinggi yang ditemukan untuk id ini.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $log_tinggi
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error', 'message' => $e->getMessage()], 500);
        }
    }


    public function input_penanaman(Request $request)
    {
        try {
            $data = $request->validate([
                'id_user' => 'required',
                'id_lahan' => 'required',
                'keterangan' => 'required',
                // 'id_device' => 'required',
                'nama_penanaman' => 'required',
                'jenis_tanaman' => 'required',
                'tanggal_tanam' => 'required',
                'tanggal_panen' => 'nullable|date', 
            ]);
            $existingPenanaman = Penanaman::where('id_lahan', $data['id_lahan'])->first();
            $lahan = Lahan::where('id', $data['id_lahan'])->first();

            if (!$lahan) {
                throw ValidationException::withMessages([
                    'id_lahan' => 'Lahan dengan ID ini tidak ditemukan.'
                ]);
            }
            if ($existingPenanaman) {
                throw ValidationException::withMessages([
                    'error' => 'Lahan dengan ID ini sudah digunakan untuk penanaman lain.'
                ]);
            }

            Penanaman::create([
                'id_user' => $data['id_user'],
                'id_lahan' => $data['id_lahan'],
                // 'id_device' => $data['id_device'],
                'id_device' => "CAEP0v54HFOtV1FsuyB",
                'nama_penanaman' => $data['nama_penanaman'],
                'keterangan' => $data['keterangan'],
                'jenis_tanaman' => $data['jenis_tanaman'],
                'tanggal_tanam' => $data['tanggal_tanam'],
                'tanggal_panen' => $data['tanggal_panen'] ?? null,
            ]);

            $lahan = Lahan::where('id', $data['id_lahan'])->first();
            $lahan->update(['isAssigned' => true]);

            return response()->json([
                'success' => true,
                'message'    => 'Data penanaman ditambahkan !',
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error', 'message' => $e->getMessage()], 500);
        }
    }

    public function update_tinggi(Request $request)
    {
        try {
            $data = $request->validate([
                'id_penanaman' => 'required',
                'tanggal_pencatatan' => 'required',
                'tinggi_tanaman' => 'required', // based in mm
            ]);

            $penanaman = Penanaman::find($data['id_penanaman']);
            Log::info($data);

            if ($penanaman) {
                log_tinggi::create([
                    'id_penanaman' => $data['id_penanaman'],
                    'tinggi_tanaman' => $data['tinggi_tanaman'],
                    'tanggal_pencatatan' => $data['tanggal_pencatatan'],
                ]);

                $pupuk_ml_data = [
                    "tinggi_tanaman" => $data['tinggi_tanaman'],
                    "hst" => $penanaman->hst,
                ];

                Log::info($pupuk_ml_data);

                try {
                    $response = Http::post(route('ml.fertilizer'), $pupuk_ml_data);
                    if($response['status'] == 200){
                        Fertilizer::create([
                            'id_device' => $penanaman->id_device,
                            'isOptimal' => $response['data']['tinggi_optimal'],
                            'message' => $response['data']['message'],
                            'waktu' => $response['data']['waktu'],
                        ]);
                    }
                } catch (\Throwable $th) {
                    Log::info($th);
                }
                
                return response()->json([
                    'success' => true,
                    'message'    => 'Data tinggi diupdate !',
                ], 200);
            } else {
                return response()->json(['error' => 'Server error', 'message' => 'Data penanaman tidak ditemukan!'], 500);
            }
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error', 'message' => $e->getMessage()], 500);
        }
    }
    public function update_hst(Request $request)
    {
        try {
            $data = $request->validate([
                'id_penanaman' => 'required',
                'hst' => 'required',
            ]);
            
            $penanaman = Penanaman::find($data['id_penanaman']);
            if ($penanaman){
                $penanaman -> update([
                    'hst'=>$data['hst']
                ]);

                return response()->json([
                    'success' => true,
                    'message'    => 'Data HST diupdate !',
                ], 200);
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function delete_penanaman(Request $request)
    {
        $id_penanaman = $request->query('id_penanaman');

        try {
            if (!empty($id_penanaman)) {
                $penanaman = Penanaman::where('id', $id_penanaman)->delete();
                if ($penanaman != 0) {
                    return response()->json([
                        'success' => true,
                        'data' => 'Data penanaman berhasil dihapus'
                    ], 200);
                }
                return response()->json([
                    'success' => false,
                    'data' => 'Data penanaman tidak ditemukan'
                ], 400);
            }
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error', 'message' => $e->getMessage()], 500);
        }
    }
    public function update_penanaman(Request $request)
    {
        try {
            // Validasi input, hanya field yang disertakan yang divalidasi

            $data = $request->validate([
                'id_penanaman' => 'required', // Pastikan ID penanaman disertakan untuk mencari data yang spesifik
                'id_user' => 'sometimes|required',
                'id_lahan' => 'sometimes|required',
                'id_device' => 'sometimes|required',
                'keterangan' => 'sometimes|required',
                'nama_penanaman' => 'sometimes|required',
                'jenis_tanaman' => 'sometimes|required',
                'tanggal_tanam' => 'sometimes|required',
                'tanggal_panen' => 'sometimes|required',
            ]);
            
            $penanaman = Penanaman::where('id', $data['id_penanaman']);

            if (!$penanaman) {
                return response()->json(['success' => false, 'message' => 'Data penanaman tidak ditemukan'], 404);
            }

            // Menghapus 'id_penanaman' dari array $data karena tidak perlu dalam proses update
            unset($data['id_penanaman']);

            // Perbarui hanya field yang disertakan dalam request
            $penanaman->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Data penanaman berhasil diperbarui',
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error', 'message' => $e->getMessage()], 500);
        }
    }
}
