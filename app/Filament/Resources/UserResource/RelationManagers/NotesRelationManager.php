<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NotesRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';
    protected static ?string $title = 'Notities';
    protected static ?string $modelLabel = "Notitie";
    protected static ?string $pluralLabel = "Notities";
    protected static ?string $recordTitleAttribute = 'content';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
//                Transform to enum
                Forms\Components\Select::make('type')
                    ->options([
                        'medical' => 'Medisch',
                        'general' => 'Algemeen',
                    ])
                ,
                Forms\Components\Textarea::make('content')->label("Inhoud")
                    ->columnSpanFull()
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('content')
            ->columns([
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('content')->label("Inhoud")->limit(50),
//                Tables\Columns\TextColumn::make('author.name')->label('Auteur'),
                Tables\Columns\TextColumn::make('created_at')->label("Aangemaakt op"),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
