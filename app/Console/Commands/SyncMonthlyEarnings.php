<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\SettlementService;
use Illuminate\Console\Command;

class SyncMonthlyEarnings extends Command
{
    protected $signature = 'earnings:sync
                            {--month= : Bulan spesifik (YYYY-MM), kosongkan untuk sync semua}';

    protected $description = 'Sync monthly_earnings dari transaksi yang belum di-settle (backfill)';

    public function handle(SettlementService $settlement): int
    {
        $query = Transaction::query()
            ->where('type', 'photobooth_session')
            ->where('status', 'paid')
            ->whereNull('gross_amount');

        if ($month = $this->option('month')) {
            $query->whereYear('created_at', substr($month, 0, 4))
                ->whereMonth('created_at', (int) substr($month, 5, 2));
        }

        $transactions = $query->get();
        $count = $transactions->count();

        if ($count === 0) {
            $this->info('Tidak ada transaksi yang perlu di-sync.');
            return self::SUCCESS;
        }

        $this->info("Memproses {$count} transaksi...");

        foreach ($transactions as $trx) {
            $gross = (int) ($trx->amount ?? 0);
            $settlement->recordSettlement($trx, $gross);
        }

        $this->info('Sync selesai. Penghasilan bulanan telah diperbarui.');
        return self::SUCCESS;
    }
}
