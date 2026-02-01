<x-filament::page>
    <div class="space-y-10">

        {{-- PROFILE --}}
        <x-filament::section heading="Profile Information">
            {{ $this->profileForm }}

            <div class="flex justify-end pt-4">
                <x-filament::button wire:click="saveProfile">
                    Save Profile
                </x-filament::button>
            </div>
        </x-filament::section>

        {{-- PASSWORD --}}
        <x-filament::section heading="Change Password">
            {{ $this->passwordForm }}

            <div class="flex justify-end pt-4">
                <x-filament::button color="warning" wire:click="changePassword">
                    Change Password
                </x-filament::button>
            </div>
        </x-filament::section>

        {{-- SECURITY --}}
        <x-filament::section heading="Security">
            <p class="text-sm text-gray-500">
                Log out from all devices except this one.
            </p>

            <div class="pt-4">
                <x-filament::button
                    color="danger"
                    wire:click="logoutAllDevices"
                >
                    Logout All Devices
                </x-filament::button>
            </div>
        </x-filament::section>

    </div>
</x-filament::page>
