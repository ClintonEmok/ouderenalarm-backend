<?php
namespace App\Services;

use App\Jobs\SendFcmNotificationJob;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Send a push notification to a single user.
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): void
    {
        foreach ($user->pushTokens as $token) {
            SendFcmNotificationJob::dispatch(
                $token->token,
                $title,
                $body,
                $data
            );
        }
    }

    /**
     * Send a push notification to multiple users.
     */
    public function sendToUsers(Collection|array $users, string $title, string $body, array $data = []): void
    {
        foreach ($users as $user) {
            $this->sendToUser($user, $title, $body, $data);
        }
    }

    /**
     * Send to a user and their caregivers.
     */
    public function sendToUserAndCaregivers(User $user, string $title, string $body, array $data = []): void
    {
        $all = collect([$user])->merge($user->caregivers);
        $this->sendToUsers($all, $title, $body, $data);
    }
}