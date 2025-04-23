<?php

namespace App\Filament\Customer\Resources\InviteResource\Pages;

use App\Enums\InviteStatus;
use App\Filament\Customer\Resources\InviteResource;
use App\Mail\InviteCaregiverMail;
use App\Mail\InviteExistingUserMail;
use App\Mail\InviteNewUserMail;
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

        if (! $user) {
            $user = User::create([
                'email' => $email,
                'phone_number' => $phone,
                'name' => 'Invited Caregiver',
                'password' => Hash::make(Str::random(32)),
            ]);

            $this->invitedUserWasNew = true;
        }

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


        $data['inviter_id'] = $inviter->id;
        $data['invited_user_id'] = $user->id;
        $data['token'] = Str::uuid();
        $data['status'] = InviteStatus::Pending;
        $data['expires_at'] = now()->addDays(7);

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var \App\Models\Invite $invite */
        $invite = $this->record;
        $user = $invite->invitedUser;

        if ($this->invitedUserWasNew) {
//            TODO:MAIL new user
            Mail::to($user->email)->queue(new InviteCaregiverMail($invite, $this->invitedUserWasNew));
            Log::info("New user");
        } else {
//            TODO:MAIL existinguser
            Mail::to($user->email)->queue(new InviteCaregiverMail($invite, $this->invitedUserWasNew));
            Log::info("Exisitng");

            Notification::make()
                ->title('You’ve been invited as a caregiver')
                ->body("{$invite->inviter->name} has invited you to become their caregiver.")
                ->sendToDatabase($user);
        }
    }
}
