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
        Schema::create('session_device', function (Blueprint $table) {
            $table->id();

            $table->string('session_id');
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();

            $table->timestamps();

            $table->foreign('session_id')
                ->references('id')
                ->on('booth_sessions')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_device');
    }
};
