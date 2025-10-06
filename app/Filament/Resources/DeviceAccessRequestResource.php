<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceAccessRequestResource\Pages;
use App\Filament\Resources\DeviceAccessRequestResource\RelationManagers;
use App\Models\DeviceAccessRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DeviceAccessRequestResource extends Resource
{
    protected static ?string $model = DeviceAccessRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Apparaattoegangverzoek';
    protected static ?string $pluralLabel = 'Apparaattoegangverzoeken';
    protected static ?string $modelLabel = 'Apparaattoegangverzoek';
    protected static ?string $navigationGroup = 'Devices';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user.name')
                    ->label('Requested By')
                    ->disabled()
                    ->dehydrated(false),

                Forms\Components\TextInput::make('phone_number')
                    ->label('Phone Number')
                    ->disabled()
                    ->required(),

                Forms\Components\Textarea::make('message')
                    ->label('Message')
                    ->disabled()
                    ->rows(3),

                Forms\Components\DateTimePicker::make('created_at')
                    ->label('Requested At')
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('User')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('phone_number')->label('Phone')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('message')->limit(40)->wrap()->label('Message'),
                Tables\Columns\TextColumn::make('created_at')->label('Requested At')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('approve_assign')
                    ->label('Approve & Assign')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => \App\Models\Device::where('phone_number', $record->phone_number)->whereNull('user_id')->exists())
                    ->action(function ($record) {
                        $device = \App\Models\Device::where('phone_number', $record->phone_number)->whereNull('user_id')->first();

                        if (! $device) {
                            throw new \Exception('Device not found or already assigned.');
                        }

                        $device->user_id = $record->user_id;
                        $device->save();

                        // Optional: delete request after approval
                        $record->delete();

                        \Filament\Notifications\Notification::make()
                            ->title('Device assigned')
                            ->body("Device with phone number {$device->phone_number} was assigned to {$record->user->name}.")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListDeviceAccessRequests::route('/'),
            'create' => Pages\CreateDeviceAccessRequest::route('/create'),
            'edit' => Pages\EditDeviceAccessRequest::route('/{record}/edit'),
        ];
    }
}
