@component('mail::message')
# You've been invited!

{{ $inviter->name }} has invited you to become their caregiver.

Click below to complete your registration:

@component('mail::button', ['url' => $url])
Complete Registration
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
