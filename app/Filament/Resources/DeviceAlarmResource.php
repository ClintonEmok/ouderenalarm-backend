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
use Filament\Resources\RelationManagers\RelationGroup;
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
                        'lat' => $record->device->latestLocation->latitude,
                        'lng' => $record->device->latestLocation->longitude,
                        'geojson' => $record?->geojson ? json_decode($record->geojson) : null
                    ])
                    ->visible(fn ($record) => $record->device->latestLocation !== null)
                    ->draggable(false)
                    ->showMyLocationButton(false)
                    ->clickable(false)
                    ->label('Locatie')
                    ->columnSpanFull(),
                TextEntry::make('latitude')
                    ->label('Latitude')
                    ->state(fn($record) => $record->device->latestLocation?->latitude ?? 'Geen locatie gevonden')->copyable()
                    ->copyMessage('Gekopieerd!')
                    ->copyMessageDuration(1500),
                TextEntry::make('longitude')
                    ->label('Longitude')
                    ->state(fn($record) => $record->device->latestLocation?->longitude ?? 'Geen locatie gevonden')->copyable()
                    ->copyMessage('Gekopieerd!')
                    ->copyMessageDuration(1500),
            ])->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("device.imei")->label("IMEI"),
                TextColumn::make("device.phone_number")->label("Telefoonnummer"),
                Tables\Columns\TextColumn::make('created_at')->label("Aangemaakt op"),
                Tables\Columns\ColumnGroup::make('Soort melding',[
                    Tables\Columns\IconColumn::make('fall_down_alert')->label('Valalarm'),
                    Tables\Columns\IconColumn::make('sos_alert')->label("Noodomroep"),
                ])
                //
            ])
            ->filters([
                Tables\Filters\Filter::make('fall_down_alert')->label("Is valalarm")
                    ->toggle()->query(fn (Builder $query): Builder => $query->where('fall_down_alert', true)),
                Tables\Filters\Filter::make('sos_alert')->label("Is noodomroep")
                    ->toggle()->query(fn (Builder $query): Builder => $query->where('sos_alert', true)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->defaultSort('created_at','desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationGroup::make('Extra info', [
                RelationManagers\CaregiverStatusesRelationManager::class,
                RelationManagers\NotesRelationManager::class,
            ])
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeviceAlarms::route('/'),
            'create' => Pages\CreateDeviceAlarm::route('/create'),
            'view' => Pages\ViewDeviceAlarm::route('/{record}'),
            'view-latest'=> Pages\ViewLatestAlarm::route('/customer/{record}')
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
