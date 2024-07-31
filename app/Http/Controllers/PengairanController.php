<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

use App\Models\UserDevice;
use App\Models\Irrigation;
use App\Models\SopPengairan;
use App\Models\Device;
use App\Models\Penanaman;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Http;

class PengairanController extends Controller
{
    public function get_data(Request $request)
    {
        try {
            $data = $request->validate([
                'id_user' => 'required',
            ]);

            $id_user = $data['id_user'];
            $user = UserDevice::where('id_user', $id_user)->first();
            if ($user == null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ditemukan device untuk user ini. Mohon daftarkan device dahulu !'
                ], 404);
            }
            $id_device = $user->id_device;

            if (!empty($id_device)) {
                // Mencari semua irrigation yang memiliki id_user yang sesuai
                $irrigation = Irrigation::where('id_device', $id_device)->get();

                // Memeriksa apakah hasilnya kosong
                if ($irrigation->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak ada data yang ditemukan untuk id_device ini.'
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

    public function get_latest_data(Request $request)
    {
        try {
            $data = $request->validate([
                'id_user' => 'required',
            ]);

            $id_user = $data['id_user'];
            $user = UserDevice::where('id_user', $id_user)->first();
            if ($user == null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ditemukan device untuk user ini. Mohon daftarkan device dahulu !'
                ], 404);
            }
            $id_device = $user->id_device;

            if (!empty($id_device)) {
                // Mencari data irrigation terbaru yang memiliki id_user yang sesuai berdasarkan created_at
                $latest_irrigation = Irrigation::where('id_device', $id_device)
                    ->orderBy('created_at', 'desc')
                    ->first();

                // Memeriksa apakah hasilnya kosong
                if ($latest_irrigation == null) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak ada data yang ditemukan untuk id_device ini.'
                    ], 404);
                }

                return response()->json([
                    'success' => true,
                    'data' => $latest_irrigation
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
            if (!empty($id_penanaman)) {
                // Mencari semua irrigation yang memiliki id_user yang sesuai
                $SOP = SopPengairan::where('id_penanaman', $id_penanaman)->get();

                // Memeriksa apakah hasilnya kosong
                if ($SOP->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak ada data SOP yang ditemukan untuk penanaman ini.'
                    ], 404);
                }

                return response()->json([
                    'success' => true,
                    'data' => $SOP
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

    public function input_manual(Request $request)
    {
        try {
            $data = $request->validate([
                'id_penanaman' => 'required',
                'tanggal_pengairan' => 'required',
                'volume' => 'required',
                'start' => 'required',
                'end' => 'required',
                // 'durasi' => 'required',
            ]);

            $now = Carbon::now();

            $penanaman = Penanaman::where('id', $data['id_penanaman'])->first();
            $id_device = $penanaman->id_device;
            $devices = Device::where('id_device', $id_device)->where('isActive', 1)->get();

            Log::info($id_device);

            // // paksa berhenti id device yg sama yg sedang berjalan
            if ($devices != []) {
                foreach ($devices as $device) {
                    $device->isActive = false;
                    $device->save();
                }

                $dataDownlink = ([
                    'data' => 1 . 'CLOSE'
                ]);
                Log::info($dataDownlink);   
                $responseDownlink = Http::post(route('antares.downlink'), $dataDownlink);
                Log::info("irrigation manual : force stop device");
            } else {
                Log::info("irrigation manual: no device active");
            }

            $isPending = false;
            $isActive = false;

            if ($data['start'] <= $now) {
                $isActive = true;
            } else {
                $isPending = true;
            }

            Device::create([
                'id_device' => $id_device,
                'tipe_intruksi' => 0,
                // 'durasi' => $data['durasi'],
                'start' => $data['start'],
                'isActive' => $isActive,
                'isPending' => $isPending,
                'end' => $data['end'],
                'volume' => $data['volume'],
                'mode' => 'manual'
            ]);
            return response()->json(['success' => 'Irrigation manual sent !'], 200);

        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error', 'message' => $e->getMessage()], 500);
        }
    }

    public function input_sop(Request $request)
    {
        try {
            $data = $request->validate([
                'id_penanaman' => 'required',
                'temp_min' => 'required',
                'temp_max' => 'required',
                'humidity_min' => 'required',
                'humidity_max' => 'required',
                'soil_min' => 'required',
                'soil_max' => 'required',
            ]);

            // Directly using updateOrCreate on the SopPengairan model
            $sop = SopPengairan::updateOrCreate(
                ['id_penanaman' => $data['id_penanaman']], // Key to find
                [
                    'temp_max' => $data['temp_max'], 
                    'temp_min' => $data['temp_min'],
                    'humidity_max' => $data['humidity_max'],
                    'humidity_min' => $data['humidity_min'],
                    'soil_max' => $data['soil_max'],
                    'soil_min' => $data['soil_min'],
                ] // Data to update or create
            );

            return response()->json([
                'success' => true,
                'message' => $sop->wasRecentlyCreated ? 'Data sop pengairan berhasil ditambahkan!' : 'Data sop pengairan berhasil diperbarui!',
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error', 'message' => $e->getMessage()], 500);
        }
    }
}
