<x-mail::message>
# Beste {{ $customer->name }},

Leuk dat je ons **Ouderen Alarm 14 dagen gratis** uit wilt proberen!

We zijn op het moment druk bezig om jouw pakketje in te pakken zodat je zo snel mogelijk veilig thuis kan blijven wonen.

Ter voorbereiding sturen we je alvast een voorbereidingsgids zodat je gelijk weet hoe alles werkt… wel zo fijn toch? :-)

---

## Inloggen

Je kunt inloggen op [www.ouderen-alarmering.nl](https://www.ouderen-alarmering.nl)

<img src="{{ asset('images/email/inloggen.png') }}" alt="Inloggen Illustratie" style="max-width: 100%; height: auto; margin: 20px 0;">

**E-mailadres:**
{{ $customer->email }}

**Wachtwoord:**
{{ $password }}

Je kunt je wachtwoord later zelf aanpassen in het portaal.

<img src="{{ asset('images/email/wachtwoord.png') }}" alt="Wachtwoord Aanpassen" style="max-width: 100%; height: auto; margin: 20px 0;">

---

## Familieleden toevoegen

Je kunt eenvoudig familieleden toevoegen in het portaal. Je bepaalt zelf wie als eerste wordt gebeld bij een noodgeval (nummer 1 = eerste contactpersoon).

<img src="{{ asset('images/email/familieleden.png') }}" alt="Familie Toevoegen" style="max-width: 100%; height: auto; margin: 20px 0;">

Als er iets gebeurt, nemen onze veiligheidsexperts binnen **30 seconden** contact op. Je ziet live in het portaal wat er is gebeurd en wie er onderweg is.

<img src="{{ asset('images/email/noodmelding.png') }}" alt="Live Alarm Inzicht" style="max-width: 100%; height: auto; margin: 20px 0;">

---

Wij koppelen het apparaat alvast aan jouw account zodat je het direct kunt gebruiken zodra het wordt geleverd.

<x-mail::button :url="'https://api.ouderen-alarmering.nl/customer/login'">
Log direct in
</x-mail::button>

Nogmaals bedankt, en we wensen jou en je familieleden een veilige en geruststellende toekomst!

Groet,
**Team OuderenAlarm**

</x-mail::message>