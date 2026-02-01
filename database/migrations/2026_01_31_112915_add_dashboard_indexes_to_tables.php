<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // booth_sessions table indexes
        Schema::table('booth_sessions', function (Blueprint $table) {
            $table->index(['project_id', 'created_at'], 'idx_project_created');
            $table->index('status', 'idx_status');
        });

        // transactions table indexes
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['session_id', 'created_at'], 'idx_session_created');
            $table->index('status', 'idx_trans_status');
        });

        // media table indexes
        Schema::table('media', function (Blueprint $table) {
            $table->index('session_id', 'idx_media_session');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booth_sessions', function (Blueprint $table) {
            $table->dropIndex('idx_project_created');
            $table->dropIndex('idx_status');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_session_created');
            $table->dropIndex('idx_trans_status');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex('idx_media_session');
        });
    }
};
