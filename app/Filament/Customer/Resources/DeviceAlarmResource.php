<?php

namespace App\Filament\Customer\Resources;

use App\Filament\Customer\Resources\DeviceAlarmResource\Pages\CreateDeviceAlarm;
use App\Filament\Customer\Resources\DeviceAlarmResource\Pages\ListDeviceAlarms;
use App\Filament\Customer\Resources\DeviceAlarmResource\Pages\ViewDeviceAlarm;
use App\Models\DeviceAlarm;
use Dotswan\MapPicker\Infolists\MapEntry;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DeviceAlarmResource extends Resource
{
    protected static ?string $model = DeviceAlarm::class;
    protected static ?string $modelLabel = 'Noodmelding';
    protected static ?string $pluralLabel = 'Noodmeldingen';

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

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
            Section::make("Kaart")->schema([
                MapEntry::make("location")
                    ->state(fn($record) => [
                        'lat' => $record->device->latestLocation->latitude,
                        'lng' => $record->device->latestLocation->longitude,
                        'geojson' => $record?->geojson ? json_decode($record->geojson) : null
                    ])
                    ->visible(fn($record) => $record->device->latestLocation !== null)
                    ->draggable(false)
                    ->showMyLocationButton(false)
                    ->clickable(false)
                    ->label('Locatie')
                    ->columnSpanFull(),
                TextEntry::make('latitude')
                    ->label('Latitude')
                    ->state(fn($record
                    ) => $record->device->latestLocation?->latitude ?? 'Geen locatie gevonden')->copyable()
                    ->copyMessage('Gekopieerd!')
                    ->copyMessageDuration(1500),
                TextEntry::make('longitude')
                    ->label('Longitude')
                    ->state(fn($record
                    ) => $record->device->latestLocation?->longitude ?? 'Geen locatie gevonden')->copyable()
                    ->copyMessage('Gekopieerd!')
                    ->copyMessageDuration(1500),
            ])->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ColumnGroup::make('Soort melding', [
                    TextColumn::make('triggered_alerts')->label("Meldingen"),
                ]),
                TextColumn::make('caregivers_en_route_list')
                    ->label('Wie is onderweg')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('created_at')->label("Tijdstip"),
            ])
            ->filters([
                Tables\Filters\Filter::make('fall_down_alert')->label("Is valalarm")
                    ->toggle()->query(fn(Builder $query): Builder => $query->where('fall_down_alert', true)),
                Tables\Filters\Filter::make('sos_alert')->label("Is noodomroep")
                    ->toggle()->query(fn(Builder $query): Builder => $query->where('sos_alert', true)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
//            TODO: make customer version
//            RelationGroup::make('Extra info', [
//                RelationManagers\CaregiverStatusesRelationManager::class,
//                RelationManagers\NotesRelationManager::class,
//            ])
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeviceAlarms::route('/'),
            'create' => CreateDeviceAlarm::route('/create'),
            'view' => ViewDeviceAlarm::route('/{record}'),
        ];
    }
}
