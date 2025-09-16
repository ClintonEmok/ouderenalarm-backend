<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Filament\Resources\UserResource\RelationManagers\CaregiversRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\DevicesRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\NotesRelationManager;
use App\Models\Country;
use App\Models\Customer;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;
    protected static ?string $modelLabel = 'Klant';
    protected static ?string $pluralModelLabel = 'Klanten';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('addresses')->whereHas('roles', function ($query) {
            $query->where('name', 'customer');
        });
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Naam')
                            ->maxLength(255)
                            ->required(),

                        Forms\Components\TextInput::make('email')
                            ->label('E-mailadres')
                            ->required()
                            ->email()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        PhoneInput::make('phone_number')
                            ->label('Telefoonnummer'),


                        Forms\Components\DatePicker::make('birthday')
                            ->label("Geboortedatum")
                            ->maxDate('today'),
                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => fn (?User $record) => $record === null ? 3 : 2]),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Gemaakt op')
                            ->content(fn (User $record): ?string => $record->created_at?->diffForHumans()),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Laatst aangepast op')
                            ->content(fn (User $record): ?string => $record->updated_at?->diffForHumans()),
                        Forms\Components\Placeholder::make('device')->label("Heeft een apparaat?")->content(fn(User $record): ?string => $record->devices()->exists() ? 'ja' : 'nee')
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn (?User $record) => $record === null),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->filters([
                Tables\Filters\Filter::make('exclude_only_caregivers')
                    ->label('Excl. Contactpersonen')
                    ->default()
                    ->query(fn (Builder $query) => $query
                        ->where(function ($query) {
                            $query
                                ->whereHas('caregivers') // patient (include)
                                ->orWhereDoesntHave('caregiverPatients'); // not a caregiver (include)
                        })
                    ),
            ])
            ->columns([
                TextColumn::make('name')->label("Naam")->searchable(isIndividual: true),
                TextColumn::make('email')->label('E-mailadres')->searchable(isIndividual: true),
                TextColumn::make('phone_number')->label("Telefoonnummer"),
//                Tables\Columns\TextColumn::make('country')->label('Land')
//                    ->getStateUsing(fn ($record): ?string => Country::find($record->addresses->first()?->country)?->name ?? null),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            RelationManagers\AddressesRelationManager::class,
                NotesRelationManager::class,
                CaregiversRelationManager::class,
                DevicesRelationManager::class,


        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
