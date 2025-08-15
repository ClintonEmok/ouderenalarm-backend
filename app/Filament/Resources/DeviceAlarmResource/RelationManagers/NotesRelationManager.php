<?php

namespace App\Filament\Resources\DeviceAlarmResource\RelationManagers;

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

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('is_false_alarm')
                    ->required()
                    ->rules(['required', 'boolean']),


                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('selectTrueAlarm')
                        ->label('Markeer als echt alarm')
                        ->icon('heroicon-o-check-circle')
                        ->color(fn ($get) => match ($get('is_false_alarm')) {
                            false => 'success',    // selected
                            true => 'gray',        // unselected
                            default => 'success',  // default initial state
                        })
                        ->action(fn ($set) => $set('is_false_alarm', false)),

                    Forms\Components\Actions\Action::make('selectFalseAlarm')
                        ->label('Markeer als vals alarm')
                        ->icon('heroicon-o-x-circle')
                        ->color(fn ($get) => match ($get('is_false_alarm')) {
                            true => 'danger',      // selected
                            false => 'gray',       // unselected
                            default => 'danger',   // default initial state
                        })
                        ->action(fn ($set) => $set('is_false_alarm', true)),


                ])->alignCenter()->label('Beoordeling')->columnSpanFull(),
                Forms\Components\Textarea::make('note')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('note')
            ->modelLabel("notitie")
            ->pluralModelLabel("notities")
            ->columns([
                Tables\Columns\TextColumn::make('note'),
                Tables\Columns\IconColumn::make('is_false_alarm')
                    ->label('Vals Alarm')
                    ->boolean()

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->mutateFormDataUsing(function (array $data): array {
                    $data['user_id'] = auth()->id();

                    return $data;
                })

            ])
            ->actions([
//                TODO: Bring back when policies work
//                Tables\Actions\EditAction::make(),
//                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
