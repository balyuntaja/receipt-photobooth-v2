<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add settlement fields for manual monthly payout model.
     * gross_amount = customer paid (from Midtrans)
     * platform_fee = platform cut
     * owner_amount = owner net earnings
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedInteger('gross_amount')->nullable()->after('amount');
            $table->unsignedInteger('platform_fee')->default(0)->after('gross_amount');
            $table->unsignedInteger('owner_amount')->nullable()->after('platform_fee');
            $table->timestamp('paid_out_at')->nullable()->after('owner_amount');
        });

        // Backfill: existing rows use amount as gross/owner, 0 fee
        DB::table('transactions')
            ->whereNull('gross_amount')
            ->update([
                'gross_amount' => DB::raw('amount'),
                'owner_amount' => DB::raw('amount'),
            ]);
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['gross_amount', 'platform_fee', 'owner_amount', 'paid_out_at']);
        });
    }
};
