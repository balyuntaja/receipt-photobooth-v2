<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('package_name')->default('Starter');
            $table->string('period')->default('Monthly'); // Monthly, Yearly
            $table->string('status')->default('aktif');   // aktif, nonaktif, expired

            $table->timestamp('started_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->string('token', 64)->unique()->nullable();
            $table->string('device_info')->nullable();
            $table->string('source')->nullable(); // voucher, payment

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
