<x-filament::page>
    <div class="grid grid-cols-12 gap-6">

        <section class="col-span-12 space-y-12">

            {{-- GENERAL --}}
            <x-filament::section heading="General" class="mb-8">
                {{ $this->form }}

                <div class="flex justify-between pt-4">
                    <x-filament::button color="gray" wire:click="back">
                        Back
                    </x-filament::button>

                    <x-filament::button color="primary" wire:click="save">
                        Save
                    </x-filament::button>
                </div>
            </x-filament::section>

            {{-- PRICING --}}
            <x-filament::section heading="Pricing" class="mt-8 mb-8">
                {{ $this->pricingForm }}

                <div class="flex justify-between pt-4">
                    <x-filament::button color="gray" wire:click="back">
                        Back
                    </x-filament::button>

                    <x-filament::button color="primary" wire:click="savePricing">
                        Save Pricing
                    </x-filament::button>
                </div>
            </x-filament::section>

            {{-- BINGKAI --}}
            <x-filament::section heading="Bingkai">
                {{ $this->frameForm }}

                <div class="flex justify-between pt-4">
                    <x-filament::button color="gray" wire:click="back">
                        Back
                    </x-filament::button>

                    <x-filament::button color="primary" wire:click="saveFrames">
                        Save Frames
                    </x-filament::button>
                </div>
            </x-filament::section>

        </section>
    </div>
</x-filament::page>