<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE welcome_screen_components MODIFY COLUMN type ENUM('text', 'image', 'background', 'button') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE welcome_screen_components MODIFY COLUMN type ENUM('text', 'image', 'background') NOT NULL");
    }
};
