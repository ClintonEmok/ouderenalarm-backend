<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\DeviceResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;


class DevicesRelationManager extends RelationManager
{
    protected static string $relationship = 'devices';

    protected static ?string $title = "Apparaten";

    protected static ?string $modelLabel = "Apparaat";
    protected static ?string $pluralLabel = "Apparaten";

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('imei')->label('IMEI')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('imei')
            ->columns([
                Tables\Columns\TextColumn::make('imei')->label('IMEI'),
            ])
            ->recordUrl(
                fn (Model $record): string => DeviceResource::getUrl('view',['record' => $record]),
            )
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AssociateAction::make()
            ])
            ->actions([
                Tables\Actions\DissociateAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                   Tables\Actions\DissociateBulkAction::make()
                ]),
            ])->inverseRelationship('user');
    }
}
