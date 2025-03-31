<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceAlarmResource\Pages;
use App\Filament\Resources\DeviceAlarmResource\RelationManagers;
use App\Filament\Resources\DeviceAlarmResource\Widgets\RecentDeviceAlarmsWidget;
use App\Models\DeviceAlarm;
use Dotswan\MapPicker\Fields\Map;
use Dotswan\MapPicker\Infolists\MapEntry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DeviceAlarmResource extends Resource
{
    protected static ?string $model = DeviceAlarm::class;
    protected static ?string $modelLabel = 'Melding';
    protected static ?string $pluralLabel = 'Noodmelding';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make("Klantendetails")->schema([
                TextEntry::make("device.user.name")->label("Naam"),
                TextEntry::make("device.phone_number")->label("Telefoonnummer")
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
                TextEntry::make("nolocation")->placeholder("Geen locatie gevonden")->label("")
            ])->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("device.imei"),
                TextColumn::make("device.phone_number")->label("Telefoonnummer")
                //
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\NotesRelationManager::class,
            RelationManagers\CaregiverStatusesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeviceAlarms::route('/'),
            'create' => Pages\CreateDeviceAlarm::route('/create'),
            'view' => Pages\ViewDeviceAlarm::route('/{record}'),
//            'edit' => Pages\EditDeviceAlarm::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
       return [
            RecentDeviceAlarmsWidget::class
        ];
    }
}
