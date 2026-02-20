<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\Project;
use App\Models\Frame;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ProjectSettings extends Page implements HasForms
{
    use InteractsWithForms;


    protected static string $resource = ProjectResource::class;
    protected static string $view = 'filament.projects.settings';

    public function getSubNavigation(): array
{
    return [];
}

    /**
     * =========================
     * FORM REGISTRATION
     * =========================
     */
    protected function getForms(): array
    {
        return [
            'form',         // General
            'pricingForm',  // Pricing
            'frameForm',    // Bingkai
        ];
    }

    /**
     * =========================
     * STATE
     * =========================
     */
    public Project $record;

    public array $data = [];     // General
    public array $pricing = [];  // Pricing
    public array $frames = [];   // Bingkai

    /**
     * =========================
     * MOUNT
     * =========================
     */
    public function mount(Project $record): void
    {
        $this->record = $record;

        // General
        $this->form->fill(
            $record->only([
                'name',
                'description',
                'is_active',
            ])
        );

        // Pricing
        $setting = $record->setting;
        $copyPrices = $setting?->copy_prices ?? [];
        $this->pricing = $setting
            ? array_merge($setting->only([
                'price_per_session',
                'copies',
                'max_retakes',
                'countdown_seconds',
                'auto_print',
            ]), [
                'price_1_copies' => $copyPrices['1'] ?? $setting->price_per_session ?? 0,
                'price_2_copies' => $copyPrices['2'] ?? null,
                'price_3_copies' => $copyPrices['3'] ?? null,
                'price_4_copies' => $copyPrices['4'] ?? null,
                'price_5_copies' => $copyPrices['5'] ?? null,
            ])
            : [
                'price_per_session' => 0,
                'copies' => 1,
                'price_1_copies' => 0,
                'price_2_copies' => null,
                'price_3_copies' => null,
                'price_4_copies' => null,
                'price_5_copies' => null,
                'max_retakes' => 1,
                'countdown_seconds' => 3,
                'auto_print' => true,
            ];

        // Frames
        $this->frames = $record->frames()
            ->pluck('frames.id')
            ->toArray();
    }

    /**
     * =========================
     * GENERAL FORM
     * =========================
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Project Name')
                    ->required(),

                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->rows(3),

                Forms\Components\Toggle::make('is_active')
                    ->label('Project Active'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $this->record->update($this->form->getState());
    }

    /**
     * =========================
     * PRICING FORM
     * =========================
     */
    public function pricingForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('copy_prices_info')
                    ->label('Harga per Jumlah Print')
                    ->content('Atur harga untuk 1x, 2x, 3x, dst print. Tamu memilih jumlah print di layar pembayaran.'),

                Forms\Components\Grid::make(5)
                    ->schema([
                        Forms\Components\TextInput::make('price_1_copies')
                            ->label('1x print')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        Forms\Components\TextInput::make('price_2_copies')
                            ->label('2x print')
                            ->numeric()
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('price_3_copies')
                            ->label('3x print')
                            ->numeric()
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('price_4_copies')
                            ->label('4x print')
                            ->numeric()
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('price_5_copies')
                            ->label('5x print')
                            ->numeric()
                            ->prefix('Rp'),
                    ]),

                Forms\Components\TextInput::make('copies')
                    ->label('Default Print Copies (jika tidak pakai copy_prices)')
                    ->helperText('Digunakan jika copy_prices kosong. Biarkan 1.')
                    ->numeric()
                    ->minValue(1)
                    ->default(1),

                Forms\Components\TextInput::make('max_retakes')
                    ->label('Max Retakes')
                    ->numeric()
                    ->minValue(0)
                    ->required(),

                Forms\Components\TextInput::make('countdown_seconds')
                    ->label('Timer Take Photo (detik)')
                    ->helperText('Durasi hitung mundur sebelum foto diambil (1–10 detik)')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(10)
                    ->default(3)
                    ->required(),

                Forms\Components\Toggle::make('auto_print')
                    ->label('Auto Print After Session'),
            ])
            ->statePath('pricing');
    }

    public function savePricing(): void
    {
        $pricing = $this->pricing;
        $copyPrices = [];
        foreach ([1, 2, 3, 4, 5] as $n) {
            $val = $pricing["price_{$n}_copies"] ?? null;
            if ($val !== null && $val !== '') {
                $copyPrices[(string) $n] = (float) $val;
            }
        }
        if (empty($copyPrices)) {
            $copyPrices = [1 => (float) ($pricing['price_per_session'] ?? 0)];
        }
        $pricing['copy_prices'] = $copyPrices;
        $pricing['price_per_session'] = $copyPrices['1'] ?? (float) ($pricing['price_per_session'] ?? 0);
        unset(
            $pricing['price_1_copies'],
            $pricing['price_2_copies'],
            $pricing['price_3_copies'],
            $pricing['price_4_copies'],
            $pricing['price_5_copies']
        );
        $this->record->setting()->updateOrCreate(
            ['project_id' => $this->record->id],
            $pricing
        );
    }

    /**
     * =========================
     * FRAME FORM
     * =========================
     */
    public function frameForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\CheckboxList::make('frames')
                    ->label('Available Frames')
                    ->options(
                        Frame::where('user_id', Auth::id())
                            ->where('is_active', true)
                            ->pluck('name', 'id')
                    )
                    ->columns(2)
                    ->helperText('Select frames available for this project'),
            ])
            ->statePath('frames');
    }

    public function saveFrames(): void
    {
        $this->record->frames()->sync(
            collect($this->frames)
                ->mapWithKeys(fn($id) => [
                    $id => ['is_active' => true],
                ])
                ->toArray()
        );
    }

    /**
     * Frames yang bisa dipilih untuk project (untuk tampilan dengan preview).
     */
    public function getAvailableFrames()
    {
        return Frame::where('user_id', Auth::id())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Save all sections (General, Pricing, Frames) at once.
     */
    public function saveAll(): void
    {
        $this->save();
        $this->savePricing();
        $this->saveFrames();

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }

    /**
     * =========================
     * BACK
     * =========================
     */
    public function back()
    {
        return redirect()->route(
            'filament.admin.resources.projects.index'
        );
    }
    
}
