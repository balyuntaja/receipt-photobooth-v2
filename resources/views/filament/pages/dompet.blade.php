<x-filament-panels::page>
    {{-- Manual Settlement Notice --}}
    <div class="mb-6 rounded-xl border border-primary-200 bg-primary-50 p-4 dark:border-primary-800 dark:bg-primary-900/20">
        <p class="text-sm font-medium text-primary-800 dark:text-primary-300">
            Pencairan dilakukan setiap akhir bulan.
        </p>
    </div>

    {{-- Current Month Earnings --}}
    @php $earnings = $this->getCurrentMonthEarnings(); @endphp
    <div class="mb-8 rounded-xl border border-gray-200 bg-linear-to-br from-primary-50 to-white p-6 dark:border-gray-700 dark:from-gray-800 dark:to-gray-900">
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Penghasilan bulan ini</p>
        <p class="mt-1 text-3xl font-bold text-primary-600 dark:text-primary-400">
            Rp {{ number_format($earnings['total_net'], 0, ',', '.') }}
        </p>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Kotor: Rp {{ number_format($earnings['total_gross'], 0, ',', '.') }}
            &bull; Biaya platform: Rp {{ number_format($earnings['total_fee'], 0, ',', '.') }}
        </p>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
            Status: {{ $earnings['payout_status'] === 'paid' ? 'Sudah dicairkan' : 'Menunggu akhir bulan' }}
        </p>
    </div>

    {{-- Riwayat penghasilan bulanan --}}
    <div>
        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Riwayat penghasilan bulanan</h2>
        @if($this->getMonthlyEarningsHistory()->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada data penghasilan.</p>
        @else
            <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Bulan</th>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Kotor</th>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Biaya</th>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Bersih</th>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                        @foreach($this->getMonthlyEarningsHistory() as $m)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($m->month . '-01')->translatedFormat('F Y') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">Rp {{ number_format($m->total_gross, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">Rp {{ number_format($m->total_fee, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">Rp {{ number_format($m->total_net, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $m->payout_status === 'paid' ? 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' : 'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400' }}">
                                        {{ $m->payout_status === 'paid' ? 'Sudah dicairkan' : 'Menunggu' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</x-filament-panels::page>
