<x-mail::message>
# Beste {{ $caregiver->name }},

Je bent toegevoegd als **contactpersoon** bij OuderenAlarm.

Dit betekent dat je meldingen ontvangt als er iets gebeurt met de persoon die jou als mantelzorger heeft opgegeven.

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
{{ $caregiver->email }}

**Wachtwoord:**
{{ $password }}

Je kunt je wachtwoord aanpassen na het inloggen via de app.

Als contactpersoon kun je:
- De status van meldingen bekijken
- Zien wie al is gebeld
- Zelf contact opnemen als dat nodig is


Dank voor je betrokkenheid!

Groet,
**Team OuderenAlarm**
</x-mail::message>