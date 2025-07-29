<x-mail::message>
# Beste {{ $customer->name }},

Leuk dat je ons **Ouderen Alarm 14 dagen gratis** uit wilt proberen!

We zijn op het moment druk bezig om jouw pakketje in te pakken zodat je zo snel mogelijk veilig thuis kan blijven wonen.

Ter voorbereiding sturen we je alvast een voorbereidingsgids zodat je gelijk weet hoe alles werkt… wel zo fijn toch? :-)

---

## Inloggen

Gebruik de OuderenAlarm app om direct meldingen te ontvangen en snel te reageren.

<x-mail::button :url="'https://play.google.com/store/apps/details?id=com.clintonemok.ouderenalarm'">
Download voor Android
</x-mail::button>

<x-mail::button :url="'https://apps.apple.com/nl/app/ouderenalarm/id6746759752?l=en-GB'">
Download voor iPhone
</x-mail::button>


**E-mailadres:**
{{ $customer->email }}

**Wachtwoord:**
{{ $password }}

Je kunt je wachtwoord later zelf aanpassen in de app.

<img src="{{ asset('images/app/wachtwoord.jpeg') }}" alt="Wachtwoord Aanpassen" style="max-width: 100%; height: auto; margin: 20px 0;">

---

## Familieleden toevoegen

U kunt eenvoudig familieleden / thuiszorg / huisarts / etc toevoegen in de app.
U bepaalt zelf wie als eerste wordt gebeld bij een noodgeval (nummer 1 = eerste contactpersoon).

---

Status controleren:

Als er iets gebeurt, nemen onze veiligheidsexperts binnen 30 seconden contact op.
Uw contactpersonen zien live in het portaal wat er is gebeurd en wie er onderweg is.

Voor extra veiligheid kunnen uw contactpersonen ook altijd controleren hoe vol het apparaatje is en zien waar u bent!

Wij koppelen het apparaat alvast aan uw account zodat u het direct kunt gebruiken zodra het geleverd wordt!

Bij vragen kunt u altijd mailen naar dit e-mail adres
(support@ouderen-alarmering.nl)


Groet,
**Team OuderenAlarm**

</x-mail::message>