<?php
namespace App\Livewire;
use App\Enums\AddressType;
use Jeffgreco13\FilamentBreezy\Livewire\MyProfileComponent;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;

class AddressesProfileComponent extends MyProfileComponent
{
    protected string $view = "livewire.addresses-profile-component";
    public array $only = ['my_custom_field'];
    public array $data;
    public $user;
    public $userClass;

    // this example shows an additional field we want to capture and save on the user
    public function mount()
    {
        $this->user = Filament::getCurrentPanel()->auth()->user();
        $this->userClass = get_class($this->user);

        $this->form->fill($this->user->only($this->only));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Repeater::make('userAddresses')
                    ->relationship('userAddresses')
                    ->schema([
                        Select::make('address_id')
                            ->label('Address')
                            ->relationship('address', 'street')
                            ->searchable()
                            ->preload()
                            ->columnSpanFull()
                            ->createOptionForm([
                                TextInput::make('full_name')->required(),
                                TextInput::make('street')->required(),
                                TextInput::make('house_number')->required(),
                                TextInput::make('postal_code')->required(),
                                TextInput::make('state'),
                                TextInput::make('city')->required(),
                                TextInput::make('country')->required(),
                            ])

                            ->required(),

                        Select::make('type')
                            ->label('Usage Type')
                            ->options(collect(AddressType::cases())->mapWithKeys(fn ($case) => [
                                $case->value => $case->label()
                            ])->toArray())->columnSpanFull()
                            ->required(),
                    ])
                    ->columns(2)
                    ->addActionLabel('Add Address')
                    ->deletable()
                    ->maxItems(2)
                    ->reorderable(),
            ])
            ->statePath('data') 
            ->model($this->user);
    }

    public function submit(): void
    {
        $state = $this->form->getState();

        $types = collect($state['userAddresses'] ?? [])->pluck('type');


        $duplicates = $types->duplicates();

        if ($duplicates->isNotEmpty()) {
            Notification::make()
                ->danger()
                ->title('Adres type moet uniek zijn')
                ->body('Je kunt slechts één factuuradres en één verzendadres toevoegen.')
                ->send();

            return;
        }

        // Save changes
        $this->form->getState();

        Notification::make()
            ->success()
            ->title('Adressen succesvol bijgewerkt')
            ->send();
    }
}

