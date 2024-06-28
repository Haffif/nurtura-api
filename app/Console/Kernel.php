<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Scheduler\IrrigationScheduler;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            try {
                $irrigation = new IrrigationScheduler();
                $irrigation->updateDeviceStatus();
                $irrigation->pendingRunner();
            } catch (\Exception $e) {
                Log::error("Error running IrrigationScheduler: " . $e->getMessage());
            }
        })->everyMinute();

     }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
