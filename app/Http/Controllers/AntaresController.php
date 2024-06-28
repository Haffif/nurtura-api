<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Antares\ControllerDevice;
use App\Http\Controllers\Antares\CameraDevice;
use App\Http\Controllers\Antares\SensorDevice;

use Illuminate\Http\Request;

class AntaresController extends Controller
{
    public function handleAntaresCamera(Request $request)
    {
        $response = CameraDevice::handleCamera($request);
        return $response;
    }

    public function handleAntaresSensor(Request $request)
    {
        $response = SensorDevice::handleSensor($request);
        return $response;
    }

    public function handleAntaresDownlink(Request $request)
    {
        $response = ControllerDevice::handleController($request);
        return $response;
    }
}
