<?php

namespace App\Models;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Enums\InviteStatus;
use Illuminate\Support\Facades\Log;

class Invite extends Model
{
    protected $fillable = [
        'inviter_id',
        'invited_user_id',
        'email',
        'phone_number',
        'token',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'status' => InviteStatus::class,
        'expires_at' => 'datetime',
    ];

    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    public function invitedUser()
    {
        return $this->belongsTo(User::class, 'invited_user_id');
    }


    public function accept(): void
    {
        Log::info("accepted");
        if ($this->status !== InviteStatus::Pending) {
            return;
        }

        $this->inviter->caregivers()->syncWithoutDetaching([$this->invited_user_id]);
        $this->update(['status' => InviteStatus::Accepted]);


//        TODO: Translate
        Notification::make()
            ->title('Invitation accepted')
            ->body("You are now a caregiver for {$this->inviter->name}.")
            ->success()
            ->sendToDatabase($this->invitedUser);
    }

    public function acceptNew(User $user): void
    {
        if ($this->status !== InviteStatus::Pending || $this->invited_user_id) {
            return;
        }

        $this->update([
            'invited_user_id' => $user->id,
            'status' => InviteStatus::Accepted,
        ]);

        $this->inviter->caregivers()->syncWithoutDetaching([$user->id]);

        Notification::make()
            ->title('Invitation accepted')
            ->body("You are now a caregiver for {$this->inviter->name}.")
            ->success()
            ->sendToDatabase($user);
    }
    public function decline(): void
    {
        Log::info("declined");
        if ($this->status !== InviteStatus::Pending) {
            return;
        }

        $this->update(['status' => InviteStatus::Declined]);
//        TODO: Translate
        Notification::make()
            ->title('Invitation declined')
            ->body("You declined the invitation from {$this->inviter->name}.")
            ->warning()
            ->sendToDatabase($this->invitedUser);
    }
    public function declineNew(User $user): void
    {
        if ($this->status !== InviteStatus::Pending || $this->invited_user_id) {
            return;
        }

        $this->update([
            'invited_user_id' => $user->id,
            'status' => InviteStatus::Declined,
        ]);

        Notification::make()
            ->title('Invitation declined')
            ->body("You declined the invitation from {$this->inviter->name}.")
            ->warning()
            ->sendToDatabase($user);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', InviteStatus::Pending);
    }
}
