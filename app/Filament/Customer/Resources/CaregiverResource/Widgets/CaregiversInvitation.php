<?php

namespace App\Filament\Customer\Resources\CaregiverResource\Widgets;

use App\Enums\InviteStatus;
use App\Filament\Customer\Resources\InviteResource;
use App\Models\Invite;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Str;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class CaregiversInvitation extends BaseWidget
{
    protected int | string | array $columnSpan = "full";
    public function getTitle(): string
    {
        return "Uitnodigingen";
    }
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Invite::query()->pending()
            )->heading('Uitnodigingen')
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('email')
                            ->label('Email')
                            ->wrap(),

                        Tables\Columns\TextColumn::make('phone_number')
                            ->label('Telefoonnummer')
                            ->limit(20),
                    ]),

                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('status')
                            ->badge()
                            ->formatStateUsing(fn (InviteStatus $state) => $state->label())
                            ->color(fn (InviteStatus $state) => $state->color())
                            ->label('Status'),

                        Tables\Columns\TextColumn::make('created_at')
                            ->since()
                            ->label('Verstuurd op'),
                    ]),
                ]),
            ])
            ->filters([
                // (Optional) You can add invite status filters here later
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->model(Invite::class)
                    ->modelLabel(InviteResource::getModelLabel())
                    ->form([
                        TextInput::make('email')->email()->required(),
                        PhoneInput::make('phone_number')->required(),
                    ]) ->mutateFormDataUsing(function (array $data): array {
                        $data['inviter_id'] = auth()->id();
                        $data['token'] = Str::uuid();
                        return $data;
                    })
                    ->createAnother(false),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]) ->contentGrid([
                'md' => 2,

            ]);
    }
}
