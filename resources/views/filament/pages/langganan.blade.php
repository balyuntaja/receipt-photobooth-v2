@push('scripts')
<script src="https://app{{ config('midtrans.is_production') ? '' : '.sandbox' }}.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script>
    document.addEventListener('livewire:init', function () {
        Livewire.on('open-snap-payment', (event) => {
            if (typeof snap !== 'undefined' && event.snapToken) {
                snap.pay(event.snapToken, {
                    onSuccess: function () {
                        window.location.reload();
                    },
                    onPending: function () {
                        window.location.reload();
                    },
                    onError: function () {
                        window.location.reload();
                    }
                });
            }
        });
    });
</script>
@endpush

<x-filament-panels::page>
    <x-filament::modal id="add-subscription" width="md">
        <x-slot name="heading">
            Tambah Langganan
        </x-slot>

        <div class="space-y-6">
            {{-- Syarat dan Ketentuan --}}
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                <label class="flex cursor-pointer items-start gap-2">
                    <input
                        type="checkbox"
                        wire:model.live="tncAccepted"
                        class="mt-0.5 h-4 w-4 shrink-0 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">
                        Saya menerima syarat dan ketentuan langganan
                        <a
                            href="#"
                            x-data
                            x-on:click.prevent="$dispatch('open-modal', { id: 'tnc-detail' })"
                            class="text-primary-600 underline hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                            Baca syarat dan ketentuan
                        </a>
                    </span>
                </label>
            </div>

            {{-- Opsi 1: Paket Bulanan --}}
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Paket Bulanan
                </h3>
                <p class="mb-3 text-sm text-gray-600 dark:text-gray-400">
                    Langganan aplikasi 1 bulan
                </p>
                <div class="flex items-center justify-between gap-4">
                    <span class="text-lg font-bold text-primary-600 dark:text-primary-400">Rp 50.000</span>
                    @if($tncAccepted)
                        <x-filament::button
                            color="primary"
                            icon="heroicon-o-credit-card"
                            wire:click="startMonthlyPayment">
                            Bayar Sekarang
                        </x-filament::button>
                    @else
                        <x-filament::button
                            color="primary"
                            icon="heroicon-o-credit-card"
                            disabled>
                            Bayar Sekarang
                        </x-filament::button>
                    @endif
                </div>
            </div>

            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="bg-white px-2 text-gray-500 dark:bg-gray-900 dark:text-gray-400">atau</span>
                </div>
            </div>

            {{-- Opsi 2: Kode Voucher --}}
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Masukkan Kode Voucher
                </h3>
                <div class="flex gap-2">
                    <input
                        type="text"
                        wire:model="voucherCode"
                        placeholder="Masukkan kode voucher"
                        class="block w-full rounded-lg border-gray-300 py-2 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                    @if($tncAccepted)
                        <x-filament::button
                            color="primary"
                            wire:click="redeemVoucher">
                            Redeem
                        </x-filament::button>
                    @else
                        <x-filament::button
                            color="primary"
                            disabled>
                            Redeem
                        </x-filament::button>
                    @endif
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Contoh: VOUCHER-BOOTH1, VOUCHER-BOOTH2, VOUCHER-BOOTH3
                </p>
            </div>
        </div>
    </x-filament::modal>

    {{-- Modal detail TnC --}}
    <x-filament::modal id="tnc-detail" width="lg">
        <x-slot name="heading">
            Syarat dan Ketentuan Langganan
        </x-slot>

        <div class="max-h-[70vh] overflow-y-auto rounded border border-gray-200 bg-white p-4 text-sm text-gray-600 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-400">
            <p class="mb-3">Dengan melanjutkan pembayaran atau menggunakan kode voucher, Anda menyetujui syarat dan ketentuan berikut:</p>
            <ul class="list-inside list-disc space-y-2">
                <li>Pembayaran diproses melalui Midtrans sebagai payment gateway.</li>
                <li>Transaksi yang sudah berhasil (paid/settlement) tidak dapat dibatalkan.</li>
                <li>Pengembalian dana (refund) mengikuti kebijakan merchant dan Midtrans.</li>
                <li>Data pembayaran Anda akan diproses sesuai kebijakan privasi kami.</li>
                <li>Merchant bertanggung jawab atas layanan yang dibeli setelah pembayaran berhasil.</li>
                <li>Pastikan data yang dimasukkan (email, metode pembayaran) benar dan valid.</li>
            </ul>
            <p class="mt-4">Syarat dan ketentuan lengkap dapat dilihat pada dokumen TnC Midtrans yang berlaku.</p>
        </div>
    </x-filament::modal>

    {{-- Table --}}
    <x-filament::section class="shadow-sm">
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-3">Paket</th>
                        <th class="px-4 py-3">Periode</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Waktu Dimulai</th>
                        <th class="px-4 py-3">Masa Berakhir</th>
                        <th class="px-4 py-3">Token</th>
                        <th class="px-4 py-3">Perangkat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($this->getSubscriptions() as $sub)
                        <tr class="bg-white dark:bg-gray-900">
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 text-gray-900 dark:text-white">
                                    <x-filament::icon icon="heroicon-o-cpu-chip" class="h-4 w-4 text-emerald-500" />
                                    {{ $sub->package_name }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $sub->period }}</td>
                            <td class="px-4 py-3">
                                @if ($sub->isActive())
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $sub->started_at?->format('n/j/Y') ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $sub->ends_at?->format('n/j/Y') ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1">
                                    <code class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-800 dark:bg-gray-800 dark:text-gray-200">
                                        {{ Str::limit($sub->token ?? '-', 16) }}
                                    </code>
                                    <button
                                        type="button"
                                        x-data
                                        x-on:click="navigator.clipboard?.writeText('{{ $sub->token }}')"
                                        wire:click="copyToken('{{ $sub->token }}')"
                                        class="rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                                        title="Salin token">
                                        <x-filament::icon icon="heroicon-o-clipboard-document" class="h-4 w-4" />
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="regenerateToken({{ $sub->id }})"
                                        wire:confirm="Yakin ingin generate ulang token? Token lama tidak akan bisa dipakai di kiosk."
                                        class="rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                                        title="Regenerate token">
                                        <x-filament::icon icon="heroicon-o-arrow-path" class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                            <td class="max-w-[160px] truncate px-4 py-3 text-xs text-gray-500 dark:text-gray-400" title="{{ $sub->device_info }}">
                                {{ Str::limit($sub->device_info ?? '-', 40) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                Belum ada langganan. Gunakan voucher untuk menambah.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
