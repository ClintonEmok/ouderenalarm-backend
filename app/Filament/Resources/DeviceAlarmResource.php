<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceAlarmResource\Pages;
use App\Filament\Resources\DeviceAlarmResource\RelationManagers;
use App\Models\DeviceAlarm;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DeviceAlarmResource extends Resource
{
    protected static ?string $model = DeviceAlarm::class;

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
            Section::make("KlantenDetails")->collapsible(),
            Section::make("ContactPersonen")->collapsible(),
//            Add map entry
            Section::make("Kaart")->collapsible(),
            Section::make("Historie van meldingen")->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeviceAlarms::route('/'),
            'create' => Pages\CreateDeviceAlarm::route('/create'),
            'view' => Pages\ViewDeviceAlarm::route('/{record}'),
            'edit' => Pages\EditDeviceAlarm::route('/{record}/edit'),
        ];
    }
}
