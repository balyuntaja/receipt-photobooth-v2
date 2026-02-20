<?php

namespace App\Filament\Pages;

use App\Models\UserBankAccount;
use App\Services\EarningsQueryService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Dompet extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';

    protected static ?string $navigationLabel = 'Penghasilan';

    protected static ?string $title = 'Penghasilan';

    protected static ?string $navigationGroup = 'Account';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.dompet';

    public int $withdrawAmount = 0;

    public ?int $selectedBankAccountId = null;

    public string $newBankCode = 'bca';

    public string $newAccountNumber = '';

    public string $newAccountName = '';

    public bool $newIsDefault = false;

    protected static array $bankCodes = [
        'bca' => 'BCA',
        'bni' => 'BNI',
        'mandiri' => 'Mandiri',
        'bri' => 'BRI',
        'permata' => 'Permata',
        'cimb' => 'CIMB Niaga',
        'danamon' => 'Danamon',
    ];

    public function mount(): void
    {
        $user = Auth::user();
        if ($user && $user->bankAccounts()->exists()) {
            $default = $user->bankAccounts()->where('is_default', true)->first();
            $this->selectedBankAccountId = $default?->id ?? $user->bankAccounts()->first()?->id;
        }
    }

    public function getWalletBalance(): int
    {
        $user = Auth::user();
        if (! $user) {
            return 0;
        }
        $wallet = $user->wallet;
        return $wallet ? $wallet->balance : 0;
    }

    /**
     * Current month earnings (manual settlement model).
     */
    public function getCurrentMonthEarnings(): array
    {
        $user = Auth::user();
        if (! $user) {
            return ['total_gross' => 0, 'total_fee' => 0, 'total_net' => 0, 'payout_status' => 'pending', 'paid_at' => null];
        }
        return app(EarningsQueryService::class)->getCurrentMonthSummary($user->id);
    }

    public function getMonthlyEarningsHistory(): \Illuminate\Support\Collection
    {
        $user = Auth::user();
        if (! $user) {
            return collect();
        }
        return app(EarningsQueryService::class)->getMonthlyHistory($user->id, 12);
    }

    public function getBankAccounts()
    {
        return Auth::user()?->bankAccounts()->orderByDesc('is_default')->orderBy('created_at')->get() ?? collect();
    }

    public function getWithdrawals()
    {
        return Auth::user()?->withdrawals()->latest()->limit(20)->get() ?? collect();
    }

    /**
     * Withdraw disabled – manual monthly settlement only.
     */
    public function withdraw(): void
    {
        try {
            $user = Auth::user();
            if (! $user) {
                return;
            }
            $bankAccount = UserBankAccount::where('user_id', $user->id)->findOrFail($this->selectedBankAccountId);
            $service = app(\App\Services\WithdrawalService::class);
            $service->withdraw($user, $this->withdrawAmount, $bankAccount);
        } catch (ValidationException $e) {
            $msg = collect($e->errors())->flatten()->first() ?? 'Gagal menarik dana';
            Notification::make()->title($msg)->danger()->send();
        }
    }

    public function addBankAccount(): void
    {
        $this->validate([
            'newBankCode' => ['required', 'string', 'in:' . implode(',', array_keys(static::$bankCodes))],
            'newAccountNumber' => ['required', 'string', 'min:8', 'max:32'],
            'newAccountName' => ['required', 'string', 'max:100'],
        ]);

        $user = Auth::user();
        if (! $user) {
            return;
        }

        if ($user->bankAccounts()->where('account_number', $this->newAccountNumber)->where('bank_code', $this->newBankCode)->exists()) {
            Notification::make()->title('Rekening sudah terdaftar')->danger()->send();
            return;
        }

        $isDefault = $this->newIsDefault || ! $user->bankAccounts()->exists();
        if ($isDefault) {
            $user->bankAccounts()->update(['is_default' => false]);
        }

        UserBankAccount::create([
            'user_id' => $user->id,
            'bank_code' => $this->newBankCode,
            'account_number' => $this->newAccountNumber,
            'account_name' => $this->newAccountName,
            'is_default' => $isDefault,
        ]);

        Notification::make()->title('Rekening ditambahkan')->success()->send();
        $this->newAccountNumber = '';
        $this->newAccountName = '';
        $this->newIsDefault = false;
        $this->dispatch('close-modal', id: 'add-bank');
        $this->mount();
    }

    public function setDefaultBank(int $id): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }
        $user->bankAccounts()->update(['is_default' => false]);
        $user->bankAccounts()->where('id', $id)->update(['is_default' => true]);
        $this->selectedBankAccountId = $id;
        Notification::make()->title('Rekening default diubah')->success()->send();
    }

    public function getBankCodes(): array
    {
        return static::$bankCodes;
    }

    public function getMinWithdraw(): int
    {
        return app(\App\Services\WithdrawalService::class)->getMinimumAmount();
    }

    protected function resetForm(): void
    {
        $this->resetValidation();
    }
}
