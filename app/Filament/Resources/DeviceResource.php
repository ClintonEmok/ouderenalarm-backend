<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceAlarmResource\Widgets\RecentDeviceAlarmsWidget;
use App\Filament\Resources\DeviceResource\Pages;
use App\Filament\Resources\DeviceResource\RelationManagers;
use App\Models\Device;
use Dotswan\MapPicker\Fields\Map;
use Dotswan\MapPicker\Infolists\MapEntry;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Squire\Models\Country;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\Infolists\PhoneEntry;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;

class DeviceResource extends Resource
{
    protected static ?string $model = Device::class;
    protected static ?string $modelLabel = 'Apparaat';
    protected static ?string $pluralLabel = 'Apparaten';

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('imei')
                    ->label('IMEI')->readOnlyOn(['edit']),
                PhoneInput::make('phone_number')->label("Telefoonnummer"),
                TextInput::make('connection_number')->label('Aansluitnummer'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                Tables\Columns\TextColumn::make('user.name')->label("Klantnaam"),
                Tables\Columns\TextColumn::make('connection_number')->label("Aansluitnummer")->searchable()->sortable(),
                Tables\Columns\TextColumn::make('phone_number')->label("Telefoonnummer")->searchable()->sortable(),
                Tables\Columns\TextColumn::make('imei')->label('IMEI')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label("Aangemaakt op")
                    ->dateTime(timezone: 'Europe/Amsterdam')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')->label("Bijgewerkt Op")
                    ->dateTime(timezone: 'Europe/Amsterdam')->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
//            TODO: Decide on whether to include or exclude house number
            Section::make("Klantendetails")->schema([
                TextEntry::make("user.name")->label("Naam"),
                TextEntry::make("user.phone_number")->label("Telefoonnummer"),
                TextEntry::make("user.email")->label("E-mailadres"),
                Section::make("Adres")->schema([
//                    TextEntry::make("user.homeAddress.full_name")->label("Naam op adres"),
                    TextEntry::make("user.homeAddress.street")->label("Straat"),
                    TextEntry::make("user.homeAddress.house_number")->label("Huisnummer"),
                    TextEntry::make("user.homeAddress.postal_code")->label("Postcode"),
                    TextEntry::make("user.homeAddress.city")->label("Stad"),
                    TextEntry::make('user.homeAddress.country')->label('Land')
                        ->formatStateUsing(fn ($state): ?string => Country::find($state)?->name ?? null),
                ])
            ])->collapsible(),
            Section::make("Apparaatdetails")->schema([
                TextEntry::make("imei")->label("IMEI"),
                TextEntry::make("phone_number")->label("Telefoonnummer"),
                TextEntry::make("connection_number")->label("Aansluitnummer"),
            ])->collapsible(),
            Section::make("Kaart")->schema([
                MapEntry::make("location")
                    ->state(fn ($record) => [
                        'lat' => $record->latestLocation->latitude,
                        'lng' => $record->latestLocation->longitude,
                        'geojson' => $record?->geojson ? json_decode($record->geojson) : null
                    ])
                    ->visible(fn ($record) => $record->latestLocation !== null)
                    ->draggable(false)
                    ->showMyLocationButton(false)
                    ->clickable(false)
                    ->label('Locatie')
                    ->columnSpanFull(),
                TextEntry::make('latitude')
                    ->label('Latitude')
                    ->state(fn($record) => $record->latestLocation?->latitude ?? 'Geen locatie gevonden')->copyable()
                    ->copyMessage('Gekopieerd!')
                    ->copyMessageDuration(1500),
                TextEntry::make('longitude')
                    ->label('Longitude')
                    ->state(fn($record) => $record->latestLocation?->longitude ?? 'Geen locatie gevonden')->copyable()
                    ->copyMessage('Gekopieerd!')
                    ->copyMessageDuration(1500),
            ])->collapsible(),

        ]);
    }

    public static function getRelations(): array
    {
        return [
            //
//            RelationManagers\GpsLocationsRelationManager::class
            RelationManagers\UserRelationManager::class
        ];
    }
    public static function getWidgets(): array
    {
        return [
            DeviceResource\Widgets\RecentDeviceAlarmsWidget::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDevices::route('/'),
            'create' => Pages\CreateDevice::route('/create'),
            'view' => Pages\ViewDevice::route('/{record}'),
            'edit' => Pages\EditDevice::route('/{record}/edit'),
        ];
    }
}
