<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CaregiverResource\Pages;
use App\Filament\Resources\CaregiverResource\RelationManagers;
use App\Models\Caregiver;
use App\Models\CaregiverPatient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CaregiverResource extends Resource
{
    protected static ?string $model = CaregiverPatient::class;
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('caregiver_id')
                    ->relationship('caregiver', 'name')
                    ->searchable()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('caregiver.name')->label('Caregiver'),
                Tables\Columns\TextColumn::make('caregiver.email')->label('Email'),
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
            'index' => Pages\ListCaregivers::route('/'),
            'create' => Pages\CreateCaregiver::route('/create'),
            'edit' => Pages\EditCaregiver::route('/{record}/edit'),
        ];
    }
}
