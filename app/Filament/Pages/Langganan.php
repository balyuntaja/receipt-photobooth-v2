<?php

namespace App\Filament\Pages;

use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Services\MidtransService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Langganan extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Langganan';

    protected static ?string $title = 'Langganan';

    protected static ?string $navigationGroup = 'Account';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.langganan';

    /**
     * Langganan/subscribe dinonaktifkan – akun hanya disediakan tanpa register.
     */
    public static function canAccess(): bool
    {
        return false;
    }

    public string $voucherCode = '';

    public bool $tncAccepted = false;

    /**
     * Dummy voucher codes - share these to users for testing.
     */
    public const DUMMY_VOUCHERS = [
        'VOUCHER-BOOTH1',
        'VOUCHER-BOOTH2',
        'VOUCHER-BOOTH3',
    ];

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('addSubscription')
                ->label('Tambah Langganan')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->action(fn () => $this->dispatch('open-modal', id: 'add-subscription')),
        ];
    }

    public function getSubscriptions()
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return collect();
        }

        return $user->subscriptions()
            ->orderByDesc('created_at')
            ->get();
    }

    public function redeemVoucher(): void
    {
        if (! $this->tncAccepted) {
            Notification::make()
                ->title('Anda harus menyetujui Syarat dan Ketentuan terlebih dahulu')
                ->danger()
                ->send();
            return;
        }

        $code = strtoupper(trim($this->voucherCode));

        if (empty($code)) {
            Notification::make()
                ->title('Kode voucher wajib diisi')
                ->danger()
                ->send();
            return;
        }

        if (! in_array($code, self::DUMMY_VOUCHERS, true)) {
            Notification::make()
                ->title('Kode voucher tidak valid')
                ->danger()
                ->send();
            return;
        }

        $subscription = $this->createSubscription('voucher');

        $this->voucherCode = '';
        $this->tncAccepted = false;

        Notification::make()
            ->title('Voucher berhasil digunakan! Token telah dibuat.')
            ->success()
            ->send();

        $this->dispatch('close-modal', id: 'add-subscription');
    }

    public function startMonthlyPayment(): void
    {
        if (! $this->tncAccepted) {
            Notification::make()
                ->title('Anda harus menyetujui Syarat dan Ketentuan terlebih dahulu')
                ->danger()
                ->send();
            return;
        }

        $user = Auth::user();
        if (! $user instanceof User) {
            Notification::make()->title('Anda harus login')->danger()->send();
            return;
        }

        $orderId = 'SUB-' . $user->id . '-' . Str::random(8) . '-' . time();
        $amount = 50000;

        Transaction::create([
            'id' => $orderId,
            'order_id' => $orderId,
            'session_id' => null,
            'owner_user_id' => $user->id,
            'device_id' => null,
            'amount' => $amount,
            'discount' => 0,
            'status' => 'pending',
            'type' => 'subscription',
        ]);

        try {
            $midtrans = app(MidtransService::class);
            $snapToken = $midtrans->createTransaction([
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $amount,
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                ],
            ]);

            $this->tncAccepted = false;
            $this->dispatch('close-modal', id: 'add-subscription');
            $this->dispatch('open-snap-payment', snapToken: $snapToken);
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal membuat pembayaran: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function regenerateToken(int $subscriptionId): void
    {
        $sub = Subscription::query()
            ->where('user_id', Auth::id())
            ->where('id', $subscriptionId)
            ->firstOrFail();

        $newToken = $sub->regenerateToken();

        Notification::make()
            ->title('Token telah digenerate ulang')
            ->success()
            ->send();

        $this->dispatch('subscription-token-regenerated', token: $newToken);
    }

    public function copyToken(string $token): void
    {
        Notification::make()
            ->title('Token disalin ke clipboard')
            ->success()
            ->send();
    }

    protected function createSubscription(string $source): Subscription
    {
        $startedAt = now();
        $endsAt = $startedAt->copy()->addMonth();

        return Subscription::create([
            'user_id' => Auth::id(),
            'package_name' => 'Starter',
            'period' => 'Monthly',
            'status' => 'aktif',
            'started_at' => $startedAt,
            'ends_at' => $endsAt,
            'token' => \Illuminate\Support\Str::random(32),
            'device_info' => request()->userAgent(),
            'source' => $source,
        ]);
    }
}
