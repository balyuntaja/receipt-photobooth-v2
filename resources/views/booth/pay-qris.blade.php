{{--
  Custom QRIS payment page (Core API, no Snap).
  Fullscreen, centered, large QR (min 320px), amount, loading, polling every 3s.
  Auto redirect when paid. No countdown timer.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bayar – {{ $projectName }}</title>
    @vite(['resources/css/booth.css'])
    <style>
        .pay-qris-page { min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #fafaf9; }
        .pay-qris-card { background: #fff; border-radius: 1rem; border: 1px solid #e7e5e4; padding: 2rem; max-width: 420px; width: 100%; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
        .pay-qris-qr { min-width: 320px; min-height: 320px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; background: #fff; border-radius: 0.75rem; border: 1px solid #e7e5e4; }
        .pay-qris-qr img { width: 320px; height: 320px; display: block; }
        .pay-qris-amount { font-size: 1.5rem; font-weight: 700; color: #1c1917; margin-bottom: 0.5rem; }
        .pay-qris-hint { font-size: 0.875rem; color: #78716c; margin-bottom: 1rem; }
        .pay-qris-loading { padding: 2rem; color: #78716c; }
        .pay-qris-loading svg { animation: pay-qris-spin 0.8s linear infinite; }
        @keyframes pay-qris-spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body class="m-0 p-4 font-sans text-stone-800">
    <div class="pay-qris-page">
        <div class="pay-qris-card">
            <h1 class="text-lg font-semibold text-stone-800 mb-4">Scan QRIS untuk membayar</h1>

            <div id="pay-qris-qr-wrap" class="pay-qris-qr {{ ($qrImageDataUrl || $qrImageUrl) ? '' : 'pay-qris-loading' }}">
                @if($qrImageDataUrl)
                    <img src="{{ $qrImageDataUrl }}" alt="QRIS" width="320" height="320">
                @elseif($qrImageUrl)
                    <img id="pay-qris-img" src="{{ $qrImageUrl }}" alt="QRIS" width="320" height="320">
                @else
                    <div class="pay-qris-loading flex flex-col items-center gap-3">
                        <svg class="h-10 w-10 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Memuat QR...</span>
                    </div>
                @endif
            </div>

            <p class="pay-qris-amount" id="pay-qris-amount">{{ $amountFormatted }}</p>
            <p class="pay-qris-hint">Scan dengan aplikasi e-wallet (GoPay, dll.)</p>
            <p class="text-xs text-stone-400" id="pay-qris-status">Menunggu pembayaran...</p>

            @if(!empty($isSandbox) && !empty($simulatorQrCodeUrl))
                <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-left">
                    <p class="text-sm font-medium text-amber-900 mb-2">Testing sandbox</p>
                    <p class="text-xs text-amber-800 mb-2">Untuk simulasi bayar tanpa scan QR:</p>
                    <ol class="text-xs text-amber-800 list-decimal list-inside space-y-1 mb-3">
                        <li>Buka <a href="{{ $simulatorPageUrl }}" target="_blank" rel="noopener" class="underline">Simulator QRIS Midtrans</a> (tab baru).</li>
                        <li>Paste URL di bawah ke kolom "QR Code Image Url", lalu klik "Scan QR".</li>
                        <li>Di halaman simulator pilih "Pay" / "Bayar" untuk mensimulasikan pembayaran berhasil.</li>
                    </ol>
                    <div class="flex gap-2">
                        <input type="text" readonly value="{{ $simulatorQrCodeUrl }}" id="pay-qris-simulator-url" class="flex-1 min-w-0 rounded-lg border border-amber-200 bg-white px-2 py-1.5 text-xs text-stone-700">
                        <button type="button" id="pay-qris-copy-url" class="shrink-0 rounded-lg bg-amber-200 px-3 py-1.5 text-xs font-medium text-amber-900 hover:bg-amber-300">Copy</button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script type="application/json" id="pay-qris-config">
    {!! json_encode(['paymentStatusUrl' => $paymentStatusUrl, 'continueUrl' => $continueUrl]) !!}
    </script>
    <script>
        (function () {
            const config = JSON.parse(document.getElementById('pay-qris-config').textContent);
            const paymentStatusUrl = config.paymentStatusUrl;
            const continueUrl = config.continueUrl;
            const POLL_INTERVAL_MS = 3000;

            let pollTimer = null;

            function stopPolling() {
                if (pollTimer) {
                    clearInterval(pollTimer);
                    pollTimer = null;
                }
            }

            function checkStatus() {
                fetch(paymentStatusUrl, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (data.status === 'paid' && data.redirect_url) {
                            stopPolling();
                            document.getElementById('pay-qris-status').textContent = 'Pembayaran berhasil! Mengalihkan...';
                            window.location.href = data.redirect_url;
                        }
                    })
                    .catch(function () {});
            }

            if (continueUrl && paymentStatusUrl) {
                pollTimer = setInterval(checkStatus, POLL_INTERVAL_MS);
                checkStatus();
            }

            document.getElementById('pay-qris-img')?.addEventListener('error', function () {
                var wrap = document.getElementById('pay-qris-qr-wrap');
                if (wrap && !wrap.querySelector('.pay-qris-loading')) {
                    wrap.innerHTML = '<div class="pay-qris-loading flex flex-col items-center gap-3"><span>Gagal memuat QR. Silakan refresh.</span></div>';
                    wrap.classList.add('pay-qris-loading');
                }
            });

            document.getElementById('pay-qris-copy-url')?.addEventListener('click', function () {
                var input = document.getElementById('pay-qris-simulator-url');
                if (input) {
                    input.select();
                    input.setSelectionRange(0, 99999);
                    navigator.clipboard.writeText(input.value).then(function () {
                        var btn = document.getElementById('pay-qris-copy-url');
                        if (btn) { btn.textContent = 'Copied!'; setTimeout(function () { btn.textContent = 'Copy'; }, 1500); }
                    });
                }
            });
        })();
    </script>
</body>
</html>
