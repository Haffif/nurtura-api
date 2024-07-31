<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sop_pengairan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_penanaman');
            $table->float('temp_max')->default(0);
            $table->float('temp_min')->default(0);
            $table->float('humidity_max')->default(0);
            $table->float('humidity_min')->default(0);
            $table->float('soil_max')->default(0);
            $table->float('soil_min')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sop_pengairan');
    }
};
