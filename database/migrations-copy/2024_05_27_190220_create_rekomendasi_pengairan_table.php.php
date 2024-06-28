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
        Schema::create('rekomendasi_pengairan', function (Blueprint $table) {
            $table->id('id_rekomendasi_air');
            // $table->foreignId('id_penanaman');
            $table->boolean('nyalakan_alat');
            $table->float('jumlah_rekomendasi_ml')->nullable();
            $table->float('durasi_detik')->nullable();
            $table->foreignId('kondisi')->nullable();
            $table->foreignId('saran')->nullable();
            $table->timestamp('tanggal_rekomendasi')->useCurrent();
            // $table->foreignId('created_by');
            // $table->foreignId('updated_by')->nullable();
            // $table->foreignId('deleted_by')->nullable();
            // $table->timestamp('created_at')->useCurrent();
            // $table->timestamp('updated_at')->nullable();
            // $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekomendasi_pengairan');
    }
};