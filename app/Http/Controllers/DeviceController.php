<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\User;
use App\Models\Device;
use App\Models\UserDevice;

class DeviceController extends Controller
{
    public static function get_device(Request $request)
    {
        try {
            $data = $request->validate([
                'id_user' => 'required',
            ]);

            $id_user = $data['id_user'];
            $user = UserDevice::where('id', $id_user)->first();
            $id_device = $user->id_device;

            $devices = Device::where('id_device', $id_device)->get();
            if ($devices->isNotEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => $devices
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Data device tidak ditemukan"
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error', 'message' => $e->getMessage()], 500);
        }
    }
    public static function add_device(Request $request)
    {
        try {
            $data = $request->validate([
                'id_user' => 'required',
                'id_device' => 'required',
            ]);
            $device = UserDevice::where('id_device', $data['id_device'])->first();
            if ($device == null) {
                UserDevice::create([
                    'id_user' => $data['id_user'],
                    'id_device' => $data['id_device']
                ]);
                return response()->json([
                    'success'    => true,
                    'message'    => 'Data device ditambahkan',
                ], 201);
            } else {
                return response()->json([
                    'success'    => false,
                    'message'    => 'Device sudah ada',
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Server error', 'message' => $e->getMessage()], 500);
        }
    }
}
