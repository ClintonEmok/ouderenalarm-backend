<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Enums\AddressType;
use App\Enums\CaregiverStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Squire\Models\Country;

//use Squire\Models\Country;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';
    protected static ?string $title = "Addressen";

    protected static ?string $recordTitleAttribute = 'street';

    public function form(Form $form): Form
    {
//        TODO: add type to form
//          TODO: Add limit of 2 addresses one of each type
        return $form
            ->schema([
                Forms\Components\TextInput::make('street')->label('Straatnaam'),

                Forms\Components\TextInput::make('postal_code')->label('Postcode'),

                Forms\Components\TextInput::make('city')->label('Stad'),

                Forms\Components\TextInput::make('state')->label('Provincie'),

                Forms\Components\Select::make('country')->label('Land')
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $query) => Country::where('name', 'like', "%{$query}%")->pluck('name', 'id'))
                    ->getOptionLabelUsing(fn ($value): ?string => Country::firstWhere('id', $value)?->getAttribute('name')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('street')->label('Straatnaam'),

                Tables\Columns\TextColumn::make('postal_code')->label('Postcode'),

                Tables\Columns\TextColumn::make('city')->label('Stad'),

                Tables\Columns\TextColumn::make('country')->label('Land')
                    ->formatStateUsing(fn ($state): ?string => Country::find($state)?->name ?? null),
                Tables\Columns\TextColumn::make('type')->label('Type')->badge()->formatStateUsing(fn ($state) => AddressType::from($state)->label())->color(fn(string $state): string => AddressType::from($state)->color(),)
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make(),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->groupedBulkActions([
                Tables\Actions\DetachBulkAction::make(),
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
