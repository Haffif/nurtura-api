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
        Schema::create('log_tinggi', function (Blueprint $table) {
            $table->id();
            $table->string('id_penanaman');
            $table->float('tinggi_tanaman');
            $table->timestamps('tanggal_pencatatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_tinggi');
    }
};
