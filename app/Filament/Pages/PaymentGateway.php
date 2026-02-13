<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class PaymentGateway extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Payment Gateway';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 50;

    protected static string $view = 'filament.pages.payment-gateway';
}

