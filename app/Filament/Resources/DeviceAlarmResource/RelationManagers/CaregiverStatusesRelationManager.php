<?php

namespace App\Filament\Resources\DeviceAlarmResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CaregiverStatusesRelationManager extends RelationManager
{
    protected static string $relationship = 'caregiverStatuses';
    protected static ?string $title = 'Contactpersonen';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status')
                    ->options([
                        'assigned' => 'Assigned',
                        'en_route' => 'En Route',
                        'arrived' => 'Arrived',
                    ])
                    ->required()
                    ->label('Status'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->heading("Contactperson")
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Naam'),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('pivot.status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'en_route' => 'warning',
                        'arrived' => 'success',
                        default => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
//                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
            ]);
    }
}
