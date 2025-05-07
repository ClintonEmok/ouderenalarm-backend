<?php

namespace App\Filament\Customer\Resources;

use App\Filament\Customer\Resources\CaregiverResource\Pages;
use App\Filament\Customer\Resources\CaregiverResource\Widgets\CaregiversInvitation;
use App\Models\Caregiver;
use App\Models\CaregiverPatient;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;

class CaregiverResource extends Resource
{
    protected static ?string $model = CaregiverPatient::class;
// TODO: make custom page
// TODO: Add this table
// TODO: Add invite table
    protected static ?string $navigationLabel = 'Beheerde zorg';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    /**
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return "Mantelzorgers";
    }
    public static function getModelLabel(): string
    {
        return "Mantelzorger";
    }







    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Split::make([
                    Stack::make([
                        TextColumn::make('caregiver.name')
                            ->label('Naam')
                            ->wrap(),

                        TextColumn::make('caregiver.email')
                            ->label('Email')
                            ->limit(30),
                    ]),

                    TextColumn::make('caregiver.phone_number')
                        ->label('Telefoonnummer')
                        ->limit(20),
                ]),
            ]) ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->headerActions([
                // TODO: Add invite widget
            ])
            ->reorderable('priority')
            ->defaultSort('priority');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            CaregiversInvitation::class
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
