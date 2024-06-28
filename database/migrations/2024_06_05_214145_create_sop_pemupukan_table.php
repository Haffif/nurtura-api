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
            $table->id();
            $table->foreignId('id_penanaman');
            $table->integer('hari_setelah_tanam');
            $table->float('tinggi_tanaman_minimal_mm');
            $table->float('tinggi_tanaman_maksimal_mm');
            $table->float('jumlah_pupuk_ml')->default(0);
            $table->float('jumlah_air_ml')->default(0); 
            $table->timestamps();
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
