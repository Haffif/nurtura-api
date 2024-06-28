<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AntaresController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LahanController;
use App\Http\Controllers\PenanamanController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\Email\MailController;
use App\Http\Controllers\TanamanController;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\MachineLearningController;
use App\Http\Controllers\PengairanController;
use App\Http\Controllers\PemupukanController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return response()->json(["message" => "hello world"]);
});

Route::group([
    'prefix' => 'antares',
    'as' => 'antares.'
], function () {
    Route::group([
        'prefix' => 'webhook',
        'as' => 'webhook.'
    ], function () {
        Route::post('test_cameras', [AntaresController::class, 'handleAntaresCamera'])->name('camera');
        Route::post('testing_sensors', [AntaresController::class, 'handleAntaresSensor'])->name('sensor');
    });
    Route::post('downlink', [AntaresController::class, 'handleAntaresDownlink'])->name('downlink');
});

Route::group([
    'prefix' => 'ml',
    'as' => 'ml.'
], function () {
    Route::post('fertilizer', [MachineLearningController::class, 'fertilizer'])->name('fertilizer');
    Route::post('irrigation', [MachineLearningController::class, 'irrigation'])->name('irrigation');
    Route::post('predict', [MachineLearningController::class, 'predict'])->name('predict');
});

Route::middleware('guest')->group(function () {
    Route::group([
        'prefix' => 'auth',
    ], function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);
    });
    Route::group([
        'prefix' => 'user',
    ], function () {
        Route::post('forget-password', [MailController::class, 'send_otp_password']);
        Route::post('reset-password', [UserController::class, 'reset_password']);
        Route::put('update-password', [UserController::class, 'update_password']);
    });
});

Route::middleware('jwt.verify')->group(function () {
    Route::group(['prefix' => 'fertilizer'], function () {
        Route::get('data', [PemupukanController::class, 'get_data']);
        Route::post('input');
        Route::group([
            'prefix' => 'sop',
            'as' => 'sop.'
        ], function () {
            Route::get('data', [PemupukanController::class, 'get_sop']);
            Route::post('input', [PemupukanController::class, 'input_sop']);
        });
    });
    Route::group(['prefix' => 'irrigation'], function () {
        Route::get('data', [PengairanController::class, 'get_data']);
        Route::post('input', [PengairanController::class, 'input_manual']);
        Route::group([
            'prefix' => 'sop',
            'as' => 'sop.'
        ], function () {
            Route::get('data', [PengairanController::class, 'get_sop']);
            Route::post('input', [PengairanController::class, 'input_sop']);
        });
    });
    Route::group(['prefix' => 'sensor'], function () {
        Route::get('data', [SensorController::class, 'get_sensor']);
        Route::get('data/latest', [SensorController::class, 'get_latest_sensor']);
    });
    Route::group(['prefix' => 'device'], function () {
        Route::get('data', [DeviceController::class, 'get_device']);
        Route::post('input', [DeviceController::class, 'add_device']);
    });
    Route::group(['prefix' => 'plant'], function () {
        Route::get('data', [TanamanController::class, 'get_plant']);
    });
    Route::group(['prefix' => 'penanaman'], function () {
        Route::get('data', [PenanamanController::class, 'get_penanaman']); // get data penanaman by user id
        Route::get('tinggi', [PenanamanController::class, 'get_tinggi']); // get data tinggi
        Route::post('input', [PenanamanController::class, 'input_penanaman']); // tambah data penanaman
        Route::put('tinggi', [PenanamanController::class, 'update_tinggi']); // input manual atau update data penanaman
        Route::put('update', [PenanamanController::class, 'update_penanaman']);
        Route::delete('delete', [PenanamanController::class, 'delete_penanaman']);
    });
    Route::group(['prefix' => 'lahan'], function () {
        Route::get('data', [LahanController::class, 'get_lahan']); // get data lahan by user id
        Route::post('input', [LahanController::class, 'input_lahan']); // tambah data lahan
        Route::put('update', [LahanController::class, 'update_lahan']); // update data lahan
        Route::delete('delete', [LahanController::class, 'delete_lahan']); // delete data lahan
    });
    Route::post('/auth/refresh-token', [AuthController::class, 'refresh']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});
