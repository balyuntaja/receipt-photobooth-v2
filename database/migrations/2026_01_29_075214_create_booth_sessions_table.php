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
        Schema::create('booth_sessions', function (Blueprint $table) {
       

            $table->string('id')->primary();

            $table->uuid('project_id')->collation('utf8mb4_unicode_ci');
            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
                ->cascadeOnDelete();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_sec')->nullable();

            $table->string('status')->default('completed');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booth_sessions');
    }
};
