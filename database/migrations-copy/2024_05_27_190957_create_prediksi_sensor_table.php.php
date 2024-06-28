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
        Schema::create('prediksi_sensor', function (Blueprint $table) {
            $table->id('id_prediksi_sensor');
            $table->foreignId('id_penanaman');
            $table->float('suhu');
            $table->float('kelembapan_udara');
            $table->float('kelembapan_tanah');
            $table->timestamp('timestamp_prediksi_sensor');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prediksi_sensor');
    }
};
