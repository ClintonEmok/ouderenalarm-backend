<x-mail::message>
# Beste {{ $customer->name }},

Leuk dat je ons **Ouderen Alarm 14 dagen gratis** uit wilt proberen!

We zijn op het moment druk bezig om jouw pakketje in te pakken zodat je zo snel mogelijk veilig thuis kan blijven wonen.

Ter voorbereiding sturen we je alvast een voorbereidingsgids zodat je gelijk weet hoe alles werkt… wel zo fijn toch? :-)

---

## Inloggen

Je kunt inloggen op [www.ouderen-alarmering.nl](https://www.ouderen-alarmering.nl)

**E-mailadres:**
{{ $customer->email }}

**Wachtwoord:**
{{ $password }}

> Je kunt je wachtwoord later zelf aanpassen in het portaal.

---

## Familieleden toevoegen

Je kunt eenvoudig familieleden toevoegen in het portaal. Je bepaalt zelf wie als eerste wordt gebeld bij een noodgeval (nummer 1 = eerste contactpersoon).

Als er iets gebeurt, nemen onze veiligheidsexperts binnen **30 seconden** contact op. Je ziet live in het portaal wat er is gebeurd en wie er onderweg is.

---

Wij koppelen het apparaat alvast aan jouw account zodat je het direct kunt gebruiken zodra het wordt geleverd.

<x-mail::button :url="'https://www.ouderen-alarmering.nl'">
Log direct in
</x-mail::button>

Nogmaals bedankt, en we wensen jou en je familieleden een veilige en geruststellende toekomst!

Groet,
**Team OuderenAlarm**

</x-mail::message>