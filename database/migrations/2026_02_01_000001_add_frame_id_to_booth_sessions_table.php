<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds frame_id to booth_sessions to store the user's selected frame.
     * Required for the kiosk flow: IDLE -> FRAME (select) -> CAPTURE.
     */
    public function up(): void
    {
        Schema::table('booth_sessions', function (Blueprint $table) {
            $table->foreignId('frame_id')->nullable()->after('project_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booth_sessions', function (Blueprint $table) {
            $table->dropForeign(['frame_id']);
        });
    }
};
