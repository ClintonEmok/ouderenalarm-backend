<?php

namespace App\Filament\Customer\Resources\CaregiverResource\Widgets;

use App\Enums\InviteStatus;
use App\Filament\Customer\Resources\InviteResource;
use App\Mail\InviteCaregiverMail;
use App\Models\Invite;
use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Mail;
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
                    ->form([
                        TextInput::make('email')->email()->required(),
                        PhoneInput::make('phone_number')->required(),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        $inviter = auth()->user();
                        $email = $data['email'] ?? null;
                        $phone = $data['phone_number'] ?? null;

                        $user = User::where('email', $email)
                            ->orWhere('phone_number', $phone)
                            ->first();

                        if ($user) {
                            $hasPending = Invite::where('inviter_id', $inviter->id)
                                ->where('invited_user_id', $user->id)
                                ->where('status', InviteStatus::Pending)
                                ->exists();

                            if ($hasPending) {
                                Notification::make()
                                    ->title('Already invited')
                                    ->body('This user has already been invited and has not responded yet.')
                                    ->warning()
                                    ->send();

                                throw new \Exception('Halt creation'); // or use a cleaner way to halt
                            }

                            $data['invited_user_id'] = $user->id;
                        } else {
                            $hasPending = Invite::where('inviter_id', $inviter->id)
                                ->where('email', $email)
                                ->where('status', InviteStatus::Pending)
                                ->exists();

                            if ($hasPending) {
                                Notification::make()
                                    ->title('Already invited')
                                    ->body('This email has already been invited and has not responded yet.')
                                    ->warning()
                                    ->send();

                                throw new \Exception('Halt creation');
                            }
                        }

                        $data['inviter_id'] = $inviter->id;
                        $data['token'] = Str::uuid();
                        $data['status'] = InviteStatus::Pending;

                        return $data;
                    })
                    ->after(function (Invite $invite, array $data) {
                        if ($invite->invited_user_id) {
                            $user = $invite->invitedUser;

                            Mail::to($user->email)->queue(new InviteCaregiverMail($invite, false));

                            Notification::make()
                                ->title('You’ve been invited as a caregiver')
                                ->body("{$invite->inviter->name} has invited you to become their caregiver.")
                                ->sendToDatabase($user);
                        } else {
                            Mail::to($invite->email)->queue(new InviteCaregiverMail($invite, true));
                        }
                    })
                    ->createAnother(false)
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
