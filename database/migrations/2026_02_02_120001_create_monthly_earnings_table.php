<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Monthly earnings per user for manual payout model.
     * Aggregated from transactions, idempotent updates.
     */
    public function up(): void
    {
        Schema::create('monthly_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->char('month', 7); // YYYY-MM
            $table->unsignedBigInteger('total_gross')->default(0);
            $table->unsignedBigInteger('total_fee')->default(0);
            $table->unsignedBigInteger('total_net')->default(0);
            $table->string('payout_status')->default('pending'); // pending | paid
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'month']);
            $table->index(['month', 'payout_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_earnings');
    }
};
