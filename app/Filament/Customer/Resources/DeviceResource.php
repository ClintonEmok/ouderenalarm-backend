<?php

namespace App\Filament\Customer\Resources;

use App\Filament\Customer\Resources\DeviceResource\Pages;
use App\Filament\Customer\Resources\DeviceResource\RelationManagers;
use App\Models\Device;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class DeviceResource extends Resource
{
    protected static ?string $model = Device::class;

    protected static ?string $modelLabel = 'Apparaat';
    protected static ?string $pluralLabel = 'Apparaten';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        return static::getModel()::query()
            ->accessibleTo($user);
    }

//    TODO: use info list
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('imei')
                    ->label('IMEI')->readOnlyOn(['edit']),
                PhoneInput::make('phone_number')->label("Telefoonnummer")->disabled(),

            ]);
    }

//    TODO: introduce custom action
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
            'index' => Pages\ListDevices::route('/'),
            'create' => Pages\CreateDevice::route('/create'),
            'edit' => Pages\EditDevice::route('/{record}/edit'),
        ];
    }
}
