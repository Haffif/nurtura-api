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
        Schema::create('data_sensor', function (Blueprint $table) {
            $table->id('id');
            $table->string('id_device')->nullable();
            $table->string('id_plant')->nullable();
            $table->float('suhu')->nullable();
            $table->float('kelembapan_udara')->nullable();
            $table->float('kelembapan_tanah')->nullable();
            $table->float('ph_tanah')->nullable();
            $table->float('nitrogen')->nullable();
            $table->float('fosfor')->nullable();
            $table->float('kalium')->nullable();
            $table->timestamp('timestamp_pengukuran')->useCurrent();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_sensor');
    }
};