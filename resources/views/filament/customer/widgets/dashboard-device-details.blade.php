<x-filament::widget>
    @if ($device)
        <x-filament::section>
            <x-slot name="heading">
                Apparaatdetails
            </x-slot>
{{--TODO: Make individual widgets--}}
            <div class="flex flex-col md:flex-row gap-6">
                {{-- Apparaatnaam links --}}
                <div class="md:w-1/2">
                    <x-filament::section>
                        <x-slot name="heading">
                            <div class="text-2xl font-bold flex items-center gap-2">
{{--                                <x-heroicon-o-device-mobile class="w-6 h-6 text-primary-600" />--}}
                                {{ $device->nickname ?? $device->imei }}
                            </div>
                        </x-slot>
                        <div class="text-gray-600">
                            IMEI: {{ $device->imei }}
                        </div>
                    </x-filament::section>
                </div>

                {{-- Info kaarten rechts --}}
                <div class="md:w-1/2 space-y-4">
                    {{-- Batterij --}}
                    <x-filament::section>
                        <x-slot name="heading">
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-battery-50 class="w-5 h-5 text-green-500" />
                                Batterijniveau
                            </div>
                        </x-slot>
                        <div class="text-lg font-semibold">
                            {{ $device->battery_level ?? 'Onbekend' }}%
                        </div>
                    </x-filament::section>

                    {{-- Signaalsterkte --}}
                    <x-filament::section>
                        <x-slot name="heading">
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-signal class="w-5 h-5 text-blue-500" />
                                Signaalsterkte
                            </div>
                        </x-slot>
                        <div class="text-lg font-semibold">
                            {{ $device->signal_strength ?? 'Onbekend' }}
                        </div>
                    </x-filament::section>

                    {{-- Laatste update --}}
                    <x-filament::section>
                        <x-slot name="heading">
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-clock class="w-5 h-5 text-gray-500" />
                                Laatste update
                            </div>
                        </x-slot>
                        <div class="text-sm font-medium text-gray-800">
                            {{ optional($device->latestLocation?->updated_at)->diffForHumans() ?? 'Onbekend' }}
                        </div>
                    </x-filament::section>
                </div>
            </div>
        </x-filament::section>
    @else
        <x-filament::section>
            <x-slot name="heading">Geen apparaat</x-slot>
            <p class="text-sm text-gray-500">Er zijn geen apparaatgegevens beschikbaar.</p>
        </x-filament::section>
    @endif
</x-filament::widget>
