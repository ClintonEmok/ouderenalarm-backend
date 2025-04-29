<x-mail::message>
    # Uitnodiging als Mantelzorger

    {{ $inviter->name }} heeft je uitgenodigd om hun mantelzorger te worden.

    Je kunt op deze uitnodiging reageren via je dashboard:

    <x-mail::button :url="$url">
        Open Dashboard
    </x-mail::button>

    Met vriendelijke groet,<br>
    {{ config('app.name') }}
</x-mail::message>


