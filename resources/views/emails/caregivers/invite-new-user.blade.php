<x-mail::message>
    # Je bent uitgenodigd!

    {{ $inviter->name }} heeft je uitgenodigd om hun mantelzorger te worden.

    Klik op de knop hieronder om je registratie te voltooien:

    @component('mail::button', ['url' => $url])
        Registratie voltooien
    @endcomponent

    Bedankt,<br>
    {{ config('app.name') }}
</x-mail::message>
