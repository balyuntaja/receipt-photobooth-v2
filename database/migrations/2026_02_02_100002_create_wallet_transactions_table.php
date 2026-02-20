<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_id')->nullable()->index();
            $table->string('type', 16); // credit | debit
            $table->unsignedBigInteger('amount');
            $table->string('description')->nullable();
            $table->string('reference_type')->nullable(); // App\Models\Transaction, App\Models\Withdrawal
            $table->string('reference_id')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
