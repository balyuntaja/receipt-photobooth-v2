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
        Schema::create('project_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('project_id')->constrained()->cascadeOnDelete();

            $table->integer('price_per_session')->default(0);
            $table->integer('copies')->default(1);
            $table->integer('max_retakes')->default(1);
            $table->boolean('auto_print')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_settings');
    }
};
