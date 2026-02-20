<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('owner_user_id')->nullable()->after('session_id')
                ->constrained('users')->nullOnDelete();
            $table->foreignId('device_id')->nullable()->after('owner_user_id')
                ->constrained('devices')->nullOnDelete();

            $table->string('order_id')->nullable()->unique()->after('id');
            $table->string('payment_type')->nullable()->after('status');
            $table->json('payload')->nullable()->after('payment_type');
            $table->string('type')->nullable()->after('payload'); // 'subscription' | 'session'
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['owner_user_id']);
            $table->dropForeign(['device_id']);
            $table->dropColumn(['owner_user_id', 'device_id', 'order_id', 'payment_type', 'payload', 'type']);
        });
    }
};
