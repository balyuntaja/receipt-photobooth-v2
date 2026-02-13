<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">

        {{-- SIDEBAR: Pendapatan + Rekening Bank + Syarat & Ketentuan --}}
        <div class="space-y-4 lg:col-span-1">

            {{-- Pendapatan card --}}
            <x-filament::section class="shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                        Pendapatan
                    </h2>
                    <x-filament::button size="sm" color="primary">
                        Tarik Dana
                    </x-filament::button>
                </div>

                <div class="mt-4 space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 dark:text-gray-300">Saldo</span>
                        <span class="font-semibold text-emerald-600">
                            Rp 0
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 dark:text-gray-300">Pending</span>
                        <span class="text-gray-800 dark:text-gray-100">
                            Rp 0
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 dark:text-gray-300">Omset</span>
                        <span class="text-gray-800 dark:text-gray-100">
                            Rp 0
                        </span>
                    </div>
                </div>

                <p class="mt-4 text-xs leading-relaxed text-gray-500 dark:text-gray-400">
                    Nilai <span class="font-semibold">saldo</span> akan update setiap hari,
                    sedangkan nilai <span class="font-semibold">pending</span> akan ter‑update 1–3 hari.
                </p>
            </x-filament::section>

            {{-- Rekening Bank card --}}
            <x-filament::section class="shadow-sm">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                        Rekening Bank
                    </h2>
                    <x-filament::button size="xs" color="gray">
                        Ubah
                    </x-filament::button>
                </div>

                <div class="rounded-lg border border-dashed border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    BCA
                </div>
            </x-filament::section>

            {{-- Syarat & Ketentuan button --}}
            <x-filament::button
                color="gray"
                class="flex w-full items-center justify-center bg-black text-white hover:bg-gray-900"
            >
                Syarat &amp; Ketentuan
            </x-filament::button>
        </div>

        {{-- MAIN CONTENT: Transaksi QRIS --}}
        <div class="lg:col-span-3">
            <x-filament::section class="shadow-sm">
                <div class="mb-4 space-y-2">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                        Transaksi QRIS
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        <span class="font-semibold">Pending</span> = Transaksi yang baru masuk.
                        <span class="font-semibold">Settled</span> = Saldo transaksi sudah masuk ke akun, dan bisa dicairkan.
                    </p>
                </div>

                {{-- Search + filters --}}
                <div class="mb-4 space-y-3">
                    <input
                        type="text"
                        placeholder="Search..."
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    />

                    <div class="flex items-center gap-4 text-xs text-gray-700 dark:text-gray-300">
                        <label class="inline-flex items-center gap-1">
                            <input
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-700"
                            />
                            <span>Pending</span>
                        </label>

                        <label class="inline-flex items-center gap-1">
                            <input
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-700"
                            />
                            <span>Settled</span>
                        </label>
                    </div>
                </div>

                {{-- Table header --}}
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white text-xs dark:border-gray-700 dark:bg-gray-900">
                    <div class="grid grid-cols-4 border-b border-gray-200 bg-gray-50 px-3 py-2 font-semibold text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        <div>Status</div>
                        <div>Ref</div>
                        <div>Waktu</div>
                        <div class="text-right">Total</div>
                    </div>

                    {{-- Empty state / placeholder --}}
                    <div class="px-3 py-6 text-center text-xs text-gray-500 dark:text-gray-400">
                        Belum ada transaksi QRIS.
                    </div>
                </div>
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>

