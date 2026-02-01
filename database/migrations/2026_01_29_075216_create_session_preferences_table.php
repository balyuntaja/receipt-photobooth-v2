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
        Schema::create('session_preferences', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();

            $table->unsignedBigInteger('filter_id')->nullable();
            $table->unsignedBigInteger('frame_id')->nullable();

            $table->integer('copy_count')->default(1);
            $table->integer('retake_count')->default(0);

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
        Schema::dropIfExists('session_preferences');
    }
};
