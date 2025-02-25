<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GPSLocationResource\Pages;
use App\Filament\Resources\GPSLocationResource\RelationManagers;
use App\Models\GPSLocation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GPSLocationResource extends Resource
{
    protected static ?string $model = GPSLocation::class;
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('device.imei'),
                Tables\Columns\TextColumn::make('longitude'),
                Tables\Columns\TextColumn::make('latitude'),
            ])
            ->filters([
                //
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGPSLocations::route('/'),
            'create' => Pages\CreateGPSLocation::route('/create'),
            'edit' => Pages\EditGPSLocation::route('/{record}/edit'),
        ];
    }
}
