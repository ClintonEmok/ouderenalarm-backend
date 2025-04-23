@component('mail::message')
    # Caregiver Invitation

    {{ $inviter->name }} has invited you to become their caregiver.

    You can respond to this invitation from your dashboard:

    @component('mail::button', ['url' => $url])
        Open Dashboard
    @endcomponent

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
