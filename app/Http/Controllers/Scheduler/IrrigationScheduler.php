<?php

namespace App\Http\Controllers\Scheduler;

use App\Models\Device;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

use Carbon\Carbon;

class IrrigationScheduler
{
    public static function updateDeviceStatus(){
        try {
            $now = Carbon::now();
            $devices = Device::where('isActive', true)->get();
            if($devices->isNotEmpty()){
                foreach ($devices as $device) {
                    $start = new Carbon($device->end);
                    $startFormatted = $start->format('Y-m-d H:i');  // Mengabaikan detik
                    $nowFormatted = $now->format('Y-m-d H:i');  // Mengabaikan detik
                    
                    if ($startFormatted <= $nowFormatted) {
                        $device->isActive = false;
                        $device->save();

                        $dataDownlink = ([
                            'data' => 1 . 'CLOSE'
                        ]);

                       Http::post(route('antares.downlink'), $dataDownlink);
                         Log::info("irrigation scheduler device: update status device");       
                    } else {
                         Log::info("irrigation scheduler : no device data updated");       
                    }
                }
            } else {
                 Log::info("irrigation scheduler device: no device active");       
            }
        } catch(\Exception $e) {
            Log::error("irrigation scheduler device: ".$e->getMessage());
        }   
    }
    public static function pendingRunner()
    {
        try {
            $now = Carbon::now();
            $devices = Device::where('isPending', true)->get();

            if ($devices->isNotEmpty()) {
                foreach ($devices as $device) {
                    $start = new Carbon($device->start);
                    $startFormatted = $start->format('Y-m-d H:i');  // Mengabaikan detik
                    $nowFormatted = $now->format('Y-m-d H:i');  // Mengabaikan detik

                    if ($startFormatted <= $nowFormatted) {
                        $device->isPending = false;
                        $device->isActive = true;
                        $device->save();

                        $dataDownlink = ([
                            'data' => 1 . 'OPEN' . $device->durasi
                        ]);

                       Http::post(route('antares.downlink'), $dataDownlink);
                         Log::info("irrigation scheduler : device pending started");
                    } else {
                         Log::info("irrigation scheduler : still pending device");
                    }
                }
            } else {
                 Log::info("irrigation scheduler device: no device pending");
            }
        } catch (\Exception $e) {
            Log::error("irrigation scheduler : " . $e->getMessage());
        }
    }
}
