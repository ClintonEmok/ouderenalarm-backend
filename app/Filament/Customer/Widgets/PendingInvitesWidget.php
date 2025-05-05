<?php

namespace App\Filament\Customer\Widgets;

use App\Enums\InviteStatus;
use App\Models\Invite;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingInvitesWidget extends BaseWidget
{
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Invite::query()
            ->where('invited_user_id', auth()->id())
            ->where('status', InviteStatus::Pending)
            ->exists();
    }
    public function table(Table $table): Table
    {
        return $table
            ->heading("Uitnodigingen")
            ->query(
                Invite::query()
                    ->where('invited_user_id', auth()->id())
                    ->where('status', InviteStatus::Pending)
            )
            ->columns([
                Tables\Columns\TextColumn::make('inviter.name')->label('Uitgenodigd door'),
                Tables\Columns\TextColumn::make('created_at')->since()->label('Wanneer'),
            ])->actions([
                Tables\Actions\Action::make('accept')
                    ->label('Accepteer')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Invite $record) => $record->accept()),

                Tables\Actions\Action::make('decline')
                    ->label('Wijs af')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (Invite $record) => $record->decline()),
            ]);
    }
}
