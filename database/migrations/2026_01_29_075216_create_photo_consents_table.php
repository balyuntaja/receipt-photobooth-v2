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
        Schema::create('photo_consents', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();

            $table->boolean('is_allowed')->default(false);
            $table->timestamp('responded_at')->nullable();

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
        Schema::dropIfExists('photo_consents');
    }
};
