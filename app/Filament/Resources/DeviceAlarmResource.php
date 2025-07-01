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
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Squire\Models\Country;

class DeviceAlarmResource extends Resource
{
    protected static ?string $model = DeviceAlarm::class;
    protected static ?string $modelLabel = 'Noodmelding';
    protected static ?string $pluralLabel = 'Noodmeldingen';

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

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
            Actions::make([ Action::make('toggleFalseAlarm')
                ->label(fn ($record) => $record->is_false_alarm ? 'Markeer als echt alarm' : 'Markeer als vals alarm')
                ->icon(fn ($record) => $record->is_false_alarm ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->color(fn ($record) => $record->is_false_alarm ? 'success' : 'danger')
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update(['is_false_alarm' => ! $record->is_false_alarm]);

                    Notification::make()
                        ->title('Status gewijzigd')
                        ->body($record->is_false_alarm ? 'Alarm gemarkeerd als vals.' : 'Alarm gemarkeerd als echt.')
                        ->success()
                        ->send();
                }),]),


            Section::make("Klantendetails")->schema([
                TextEntry::make("device.user.name")->label("Naam"),
                TextEntry::make("device.phone_number")->label("Telefoonnummer"),
                TextEntry::make("device.user.email")->label("E-mailadres"),
                 Section::make("Adres")->schema([
//                     TextEntry::make("device.user.homeAddress.full_name")->label("Naam op adres"),
                     TextEntry::make("device.user.homeAddress.street")->label("Straat"),
                     TextEntry::make("device.user.homeAddress.house_number")->label("Huisnummer"),
                     TextEntry::make("device.user.homeAddress.postal_code")->label("Postcode"),
                     TextEntry::make("device.user.homeAddress.city")->label("Stad"),
                     TextEntry::make('device.user.homeAddress.country')->label('Land')
                         ->formatStateUsing(fn ($state): ?string => Country::find($state)?->name ?? null),
                 ])
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
                TextColumn::make("device.user.name")->label("Klantnaam"),
                TextColumn::make("device.connection_number")->label("Aansluitnumer"),
                TextColumn::make("device.phone_number")->label("Telefoonnummer"),
                TextColumn::make("device.imei")->label("IMEI")->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->label("Aangemaakt op")->dateTime(timezone: 'Europe/Amsterdam'),
                Tables\Columns\IconColumn::make('is_false_alarm')
                    ->boolean(),
                Tables\Columns\ColumnGroup::make('Soort melding',[
                    TextColumn::make('triggered_alerts')->label("Meldingen"),
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
//                Tables\Actions\EditAction::make(),
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
