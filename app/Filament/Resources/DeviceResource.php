<?php

namespace App\Filament\Resources;

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
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\Infolists\PhoneEntry;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;

class DeviceResource extends Resource
{
    protected static ?string $model = Device::class;
    protected static ?string $modelLabel = 'Apparaat';
    protected static ?string $pluralLabel = 'Apparaten';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('imei')
                ->label('imei')->readOnlyOn(['edit']),
                PhoneInput::make('phone_number')->label("Telefoonnummer"),
                Map::make("location")
                    ->afterStateHydrated(function ($state, $record, Forms\Set $set): void {
                        if ($record->latestLocation) {
                            $set('location', [
                                'lat' => $record->latestLocation->latitude,
                                'lng' => $record->latestLocation->longitude,
                            ]);
                        }
                    })
                    ->visible(fn ($record) => $record->latestLocation !== null)
                    ->draggable(false)
                    ->showMyLocationButton(false)
                    ->clickable(false)
                    ->label('Locatie')
                    ->columnSpanFull(),
                TextInput::make('longitude')->label('Longitude')->afterStateHydrated(function ($state, $record, Forms\Set $set): void { if($record->latestLocation){$set('longitude', $record->latestLocation->longitude);}}),
                TextInput::make('latitiude')->label('Latitude')->afterStateHydrated(function ($state, $record, Forms\Set $set): void {if($record->latestLocation){$set('latitude', $record->latestLocation->latitude);}}),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //

                Tables\Columns\TextColumn::make('imei'),
                Tables\Columns\TextColumn::make('phone_number')->label("Telefoonnummer"),
                Tables\Columns\TextColumn::make('user.name')->label("Gebruiker"),
                Tables\Columns\TextColumn::make('created_at')->label("Aangemaakt op")

                ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')->label("Bijgewerkt Op")
                ->dateTime(),

            ])
            ->filters([
                //
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

//    public static function infolist(Infolist $infolist): Infolist
//    {
//        return $infolist->schema([
//            Section::make("Klantendetails")->schema([
//                TextEntry::make("user.name")->label("Naam"),
//            ])->collapsible(),
//            Section::make("Apparaatdetails")->schema([
//                TextEntry::make("imei")->label("IMEI"),
//                TextEntry::make("device.phone_number")->label("Telefoonnummer")
//            ])->collapsible(),
//            Section::make("Kaart")->schema([
//
//                TextEntry::make("nolocation")->placeholder("Geen locatie gevonden")->label("")
//            ])->collapsible(),
//
//        ]);
//    }

    public static function getRelations(): array
    {
        return [
            //
//            RelationManagers\GpsLocationsRelationManager::class
            RelationManagers\UserRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDevices::route('/'),
            'view' => Pages\ViewDevice::route('/{record}'),
            'create' => Pages\CreateDevice::route('/create'),
            'edit' => Pages\EditDevice::route('/{record}/edit'),
        ];
    }
}
