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
        Schema::create('sop_pemupukan', function (Blueprint $table) {
            $table->id('id_sop_pemupukan');
            $table->integer('hari_setelah_tanam');
            $table->float('tinggi_tanaman_minimal_mm');
            $table->float('tinggi_tanaman_maksimal_mm');
            $table->float('jumlah_pupuk_ml');
            $table->float('jumlah_air_ml');
            $table->foreignId('created_by');
            $table->foreignId('updated_by')->nullable();
            $table->foreignId('deleted_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sop_pemupukan');
    }
};