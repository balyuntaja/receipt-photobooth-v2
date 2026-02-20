<?php

namespace App\Filament\Resources\VoucherResource\Pages;

use App\Filament\Resources\VoucherResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateVoucher extends CreateRecord
{
    protected static string $resource = VoucherResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCancelFormAction()
                ->label('Kembali'),
            Action::make('create')
                ->label('Simpan')
                ->submit('create')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['is_active'] = true; // Voucher selalu aktif saat dibuat

        return $data;
    }
}
