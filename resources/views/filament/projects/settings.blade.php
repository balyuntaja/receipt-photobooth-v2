<x-filament::page>
    <div class="grid grid-cols-12 gap-6">

        <section class="col-span-12 space-y-12">

            {{-- GENERAL --}}
            <x-filament::section heading="General" class="mb-8">
                {{ $this->form }}
            </x-filament::section>

            {{-- WELCOME SCREEN --}}
            <x-filament::section heading="Welcome Screen" class="mb-8">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Customize the welcome screen that visitors see when they open your booth.
                </p>
                @php
                    $welcomeComponents = $this->record->welcomeScreenComponents()->orderBy('sort_order')->get();
                @endphp
                @if ($welcomeComponents->isNotEmpty())
                    <div class="mb-4">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Preview</p>
                        @include('filament.projects.partials.welcome-preview', ['welcomeComponents' => $welcomeComponents, 'welcomeBackgroundColor' => $this->record->welcome_background_color ?? null])
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">No welcome screen content yet. Add background, text, and button in the editor.</p>
                @endif
                <x-filament::button
                    tag="a"
                    href="{{ route('filament.admin.resources.projects.welcome-screen', ['record' => $this->record]) }}"
                    color="primary"
                    icon="heroicon-o-paint-brush"
                >
                    Open Welcome Screen Editor
                </x-filament::button>
            </x-filament::section>

            {{-- PRICING --}}
            <x-filament::section heading="Pricing" class="mt-8 mb-8">
                {{ $this->pricingForm }}
            </x-filament::section>

            {{-- BINGKAI (Available Frames dengan preview) --}}
            <x-filament::section heading="Bingkai" class="mb-8">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Pilih frame yang tersedia untuk project ini.
                </p>
                @php
                    $availableFrames = $this->getAvailableFrames();
                @endphp
                @if ($availableFrames->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada frame. Buat frame di menu Frames terlebih dahulu.</p>
                @else
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                        @foreach ($availableFrames as $frame)
                            @php
                                $previewUrl = $frame->preview_image
                                    ? asset('storage/' . $frame->preview_image)
                                    : null;
                                $isChecked = in_array((string) $frame->id, $this->frames ?? [], true)
                                    || in_array($frame->id, $this->frames ?? [], true);
                            @endphp
                            <label
                                class="relative flex cursor-pointer flex-col overflow-hidden rounded-xl border-2 transition-colors {{ $isChecked ? 'border-primary-500 ring-2 ring-primary-400 dark:border-primary-500' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}"
                            >
                                <input
                                    type="checkbox"
                                    wire:model.live="frames"
                                    value="{{ $frame->id }}"
                                    class="absolute right-2 top-2 z-10 h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                />
                                @if ($previewUrl)
                                    <div class="aspect-[3/5] w-full bg-gray-100 dark:bg-gray-800">
                                        <img
                                            src="{{ $previewUrl }}"
                                            alt="{{ $frame->name }}"
                                            class="h-full w-full object-cover object-top"
                                        />
                                    </div>
                                @else
                                    <div class="flex aspect-[3/5] w-full items-center justify-center bg-gray-100 dark:bg-gray-800">
                                        <x-filament::icon icon="heroicon-o-photo" class="h-12 w-12 text-gray-400" />
                                    </div>
                                @endif
                                <span class="truncate px-2 py-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ $frame->name }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </x-filament::section>

            {{-- SINGLE SAVE + BACK --}}
            <div class="flex justify-between items-center pt-4 border-t border-gray-200 dark:border-gray-700">
                <x-filament::button color="gray" wire:click="back">
                    Back
                </x-filament::button>
                <x-filament::button color="primary" wire:click="saveAll">
                    Save
                </x-filament::button>
            </div>

        </section>
    </div>
</x-filament::page>