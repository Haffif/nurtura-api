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
        Schema::create('penanaman', function (Blueprint $table) {
            $table->id('id');
            $table->foreignId('id_lahan');
            $table->foreignId('id_user');
            $table->string('id_device');
            $table->string('nama_penanaman');
            $table->string('jenis_tanaman');
            $table->string('keterangan')->nullable();
            $table->date('tanggal_tanam');
            $table->date('tanggal_panen')->nullable();
            $table->integer('hst')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penanaman');
    }
};
