<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class Profile extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Profile';
    protected static ?string $navigationGroup = 'Account';
    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.profile';

    /** =====================
     * STATE
     * ===================== */
    public array $profile = [];
    public array $password = [];

    /** =====================
     * MOUNT
     * ===================== */
    public function mount(): void
    {
        $user = Auth::user();

        $this->profile = [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
        ];
    }

    /** =====================
     * FORM REGISTRATION
     * ===================== */
    protected function getForms(): array
    {
        return [
            'profileForm',
            'passwordForm',
        ];
    }

    /** =====================
     * PROFILE FORM
     * ===================== */
    public function profileForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('avatar')
                    ->label('Avatar')
                    ->image()
                    ->directory('avatars')
                    ->imagePreviewHeight(120)
                    ->circleCropper(),

                Forms\Components\TextInput::make('name')
                    ->label('Full Name')
                    ->required(),

                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required(),
            ])
            ->statePath('profile');
    }

    public function saveProfile(): void
    {
        Auth::user()->update($this->profile);

        Notification::make()
            ->title('Profile updated')
            ->success()
            ->send();
    }

    /** =====================
     * PASSWORD FORM
     * ===================== */
    public function passwordForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('current_password')
                    ->label('Current Password')
                    ->password()
                    ->required(),

                Forms\Components\TextInput::make('new_password')
                    ->label('New Password')
                    ->password()
                    ->required()
                    ->minLength(8),

                Forms\Components\TextInput::make('new_password_confirmation')
                    ->label('Confirm New Password')
                    ->password()
                    ->same('new_password')
                    ->required(),
            ])
            ->statePath('password');
    }

    public function changePassword(): void
    {
        $user = Auth::user();

        if (!Hash::check($this->password['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'password.current_password' => 'Current password is incorrect.',
            ]);
        }

        $user->update([
            'password' => Hash::make($this->password['new_password']),
        ]);

        $this->password = [];

        Notification::make()
            ->title('Password changed')
            ->success()
            ->send();
    }

    /** =====================
     * SECURITY
     * ===================== */
    public function logoutAllDevices(): void
    {
        Auth::logoutOtherDevices(request('password'));

        Notification::make()
            ->title('Logged out from all devices')
            ->success()
            ->send();
    }
}
