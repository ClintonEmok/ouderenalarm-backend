<x-filament-breezy::grid-section md=2 title="Addressen" description="This is the description">
    <x-filament::card>
        <form wire:submit.prevent="submit" class="space-y-6">

            {{ $this->form }}

            <div class="text-right">
                <x-filament::button type="submit" form="submit" class="align-right">
                    Opslaan
                </x-filament::button>
            </div>
        </form>
    </x-filament::card>
    <x-filament-actions::modals />
</x-filament-breezy::grid-section>
