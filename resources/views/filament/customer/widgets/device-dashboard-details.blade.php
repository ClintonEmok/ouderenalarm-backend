<x-filament::widget>
    <x-filament::section>
        <x-slot name="heading">
            📊 Apparaatstatus Overzicht
        </x-slot>

        <div class="flex flex-col md:flex-row gap-6">
            <div class="md:w-1/3 w-full">
                <x-filament::section>
                    <x-slot name="heading">
                        📱 Apparaatinfo
                    </x-slot>

                    @if ($device)
                        <div class="space-y-1">
                            <div class="text-sm text-gray-600">Naam</div>
                            <div class="text-lg font-semibold">{{ $device->nickname ?? 'Geen naam' }}</div>

                            <div class="text-sm text-gray-600 mt-2">IMEI</div>
                            <div class="text-base text-gray-800">{{ $device->imei }}</div>
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Geen apparaat geselecteerd.</p>
                    @endif
                </x-filament::section>
            </div>

            <div class="md:w-2/3 w-full flex flex-col space-y-4">
                <x-filament::section>
                    <x-slot name="heading">
                        🔋 Batterijniveau
                    </x-slot>

                    <div>
                        @if ($batteryLevel === null || $batteryLevel <= 20)
                            <x-heroicon-o-battery-0 class="w-6 h-6 text-red-500" />
                        @elseif ($batteryLevel > 70)
                            <x-heroicon-o-battery-100 class="w-6 h-6 text-green-500" />
                        @else
                            <x-heroicon-o-battery-50 class="w-6 h-6 text-yellow-500" />
                        @endif

                        <p class="text-gray-800 font-semibold text-lg">
                            {{ $batteryLevel !== null ? $batteryLevel . '%' : 'Onbekend' }}
                        </p>
                        <p class="text-sm text-gray-500">Battery level & chart</p>
                    </div>
                </x-filament::section>

                <x-filament::section>
                    <x-slot name="heading">
                        📶 Signaalsterkte
                    </x-slot>

                    <div>
                        <p class="text-gray-800 font-semibold text-lg">
                            {{ $signalStrength !== null ? $signalStrength ."%" : 'Onbekend' }}
                        </p>
                        <p class="text-sm text-gray-500">Verbindingssterkte</p>
                    </div>
                </x-filament::section>

                <x-filament::section>
                    <x-slot name="heading">
                        ⏱️ Laatste update
                    </x-slot>

                    <div>
                        <p class="text-gray-800 font-semibold text-lg">
                            {{ $lastUpdatedAt ? $lastUpdatedAt->locale('nl')->diffForHumans() : 'Onbekend' }}
                        </p>
{{--                        <p class="text-sm text-gray-500">Time since last data point</p>--}}
                    </div>
                </x-filament::section>
            </div>
        </div>
    </x-filament::section>
</x-filament::widget>
