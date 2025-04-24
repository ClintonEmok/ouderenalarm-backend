<?php

namespace App\Filament\Customer\Resources\InviteResource\Pages;

use App\Enums\InviteStatus;
use App\Filament\Customer\Resources\InviteResource;
use App\Mail\InviteCaregiverMail;
use App\Models\Invite;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CreateInvite extends CreateRecord
{
    protected static string $resource = InviteResource::class;

    protected bool $invitedUserWasNew = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $inviter = auth()->user();
        $email = $data['email'] ?? null;
        $phone = $data['phone_number'] ?? null;

        $user = User::where('email', $email)
            ->orWhere('phone_number', $phone)
            ->first();

        $this->invitedUserWasNew = ! $user;

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

                $this->halt();
            }

            $data['invited_user_id'] = $user->id;
        } else {
            // Check by email only for duplicate invites to non-registered users
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

                $this->halt();
            }
        }

        $data['inviter_id'] = $inviter->id;
        $data['token'] = Str::uuid();
        $data['status'] = InviteStatus::Pending;
//        $data['expires_at'] = now()->addDays(7);

        return $data;
    }
    protected function afterCreate(): void
    {
        /** @var \App\Models\Invite $invite */
        $invite = $this->record;

        if ($this->invitedUserWasNew) {
            // Send email only, no user exists yet
            Mail::to($invite->email)->queue(new InviteCaregiverMail($invite, true));
        } else {
            // Existing user
            $user = $invite->invitedUser;

            Mail::to($user->email)->queue(new InviteCaregiverMail($invite, false));

            Notification::make()
                ->title('You’ve been invited as a caregiver')
                ->body("{$invite->inviter->name} has invited you to become their caregiver.")
                ->sendToDatabase($user);
        }
    }
}
