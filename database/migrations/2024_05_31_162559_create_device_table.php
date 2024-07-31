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
        Schema::create('device', function (Blueprint $table) {
            $table->id('id');
            $table->string('id_device');
            $table->foreignId('tipe_intruksi')->default(null);
            $table->float('volume')->default(0);
            $table->boolean('isActive')->default(false);
            $table->boolean('isPending')->default(false);
            $table->string('mode');
            $table->timestamp('start');
            $table->timestamp('end');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device');
    }
};
