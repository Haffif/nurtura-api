<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use App\Models\Lahan;

class LahanController extends Controller
{
    public function input_lahan(Request $request)
    {
        try {

            $data_lahan = $request->validate([
                'id_user' => 'required',
                'nama_lahan' => 'required',
                'deskripsi' => 'required',
                'longitude' => 'required',
                'latitude' => 'required'
            ]); 

            Lahan::create([
                'id_user' => $data_lahan['id_user'],
                'nama_lahan' => $data_lahan['nama_lahan'],
                'deskripsi' => $data_lahan['deskripsi'],
                'longitude' => $data_lahan['longitude'],
                'latitude' => $data_lahan['latitude'],
            ]);

            return response()->json([
                'success' => true,
                'message'    => 'Data lahan ditambahkan !',
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error', 'message' => $e->getMessage()], 500);
        }
    }

    public function get_lahan(Request $request)
    {
        // Mendapatkan id_user dari query string
        try {
            $data = $request->validate([
                'id_user' => 'required_without_all:id_lahan',
                'id_lahan' => 'required_without_all:id_user'
            ]);

            $id_user = $request->query('id_user');
            $id_lahan = $request->query('id_lahan');

            if (!empty($id_user)) {
                $lahan = Lahan::where('id_user', $id_user)->get();
            } else {
                $lahan = Lahan::where('id', $id_lahan)->get();
            }

            if ($lahan->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada lahan yang ditemukan untuk id_user ini.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $lahan
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Menangani kesalahan server
            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update_lahan(Request $request)
    {
        try {
            // Validasi input, hanya field yang disertakan yang divalidasi
            $data = $request->validate([
                'id_lahan' => 'required', // Pastikan ID penanaman disertakan untuk mencari data yang spesifik
                'deskripsi' => 'sometimes|required',
                'longitude' => 'sometimes|required',
                'latitude' => 'sometimes|required',
            ]);

            $lahan = Lahan::find($data['id_lahan']);

            if (!$lahan) {
                return response()->json(['success' => false, 'message' => 'Data lahan tidak ditemukan'], 404);
            }

            // Menghapus 'id_penanaman' dari array $data karena tidak perlu dalam proses update
            unset($data['id_lahan']);

            // Perbarui hanya field yang disertakan dalam request
            $lahan->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Data lahan berhasil diperbarui',
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error', 'message' => $e->getMessage()], 500);
        }
    }

    public function delete_lahan(Request $request)
    {
        // Mendapatkan id_user dari query string
        $id_lahan = $request->query('id_lahan');

        try {
            if (!empty($id_lahan)) {
                // Mencari semua lahan yang memiliki id_user yang sesuai
                $lahan = Lahan::where('id', $id_lahan)->delete();
                if ($lahan != 0) {
                    return response()->json([
                        'success' => true,
                        'data' => 'Data lahan berhasil dihapus'
                    ], 200);
                }
                return response()->json([
                    'success' => false,
                    'data' => 'Data lahan tidak ditemukan'
                ], 400);
            }
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Menangani kesalahan server
            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
