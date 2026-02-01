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
                'cover_image',
                'is_active',
            ])
        );

        // Pricing
        $this->pricing = $record->setting
            ? $record->setting->only([
                'price_per_session',
                'copies',
                'max_retakes',
                'auto_print',
            ])
            : [
                'price_per_session' => 0,
                'copies' => 1,
                'max_retakes' => 1,
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

                Forms\Components\FileUpload::make('cover_image')
                    ->label('Cover Image')
                    ->image()
                    ->directory('project-covers')
                    ->imagePreviewHeight(180),

                Forms\Components\Toggle::make('is_active')
                    ->label('Project Active'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $this->record->update($this->form->getState());

        Notification::make()
            ->title('Project updated')
            ->success()
            ->send();
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
                Forms\Components\TextInput::make('price_per_session')
                    ->label('Price per Session')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                Forms\Components\TextInput::make('copies')
                    ->label('Print Copies')
                    ->numeric()
                    ->minValue(1)
                    ->required(),

                Forms\Components\TextInput::make('max_retakes')
                    ->label('Max Retakes')
                    ->numeric()
                    ->minValue(0)
                    ->required(),

                Forms\Components\Toggle::make('auto_print')
                    ->label('Auto Print After Session'),
            ])
            ->statePath('pricing');
    }

    public function savePricing(): void
    {
        $this->record->setting()->updateOrCreate(
            ['project_id' => $this->record->id],
            $this->pricing
        );

        Notification::make()
            ->title('Pricing updated')
            ->success()
            ->send();
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
                        Frame::where('user_id', auth()->id())
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

        Notification::make()
            ->title('Frames updated')
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
