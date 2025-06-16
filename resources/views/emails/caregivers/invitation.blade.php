<x-mail::message>
# Beste {{ $caregiver->name }},

Je bent toegevoegd als **contactpersoon** bij OuderenAlarm.

Dit betekent dat je meldingen ontvangt als er iets gebeurt met de persoon die jou als mantelzorger heeft opgegeven.

---

## Inloggen

Je kunt inloggen op [www.ouderen-alarmering.nl](https://www.ouderen-alarmering.nl)

**E-mailadres:**
{{ $caregiver->email }}

**Wachtwoord:**
{{ $password }}

Je kunt je wachtwoord aanpassen na het inloggen via het portaal.

<x-mail::button :url="'https://api.ouderen-alarmering.nl/caregiver/login'">
Log direct in
</x-mail::button>

---

Als contactpersoon kun je:
- De status van meldingen bekijken
- Zien wie al is gebeld
- Zelf contact opnemen als dat nodig is

Dank voor je betrokkenheid!

Groet,
**Team OuderenAlarm**
</x-mail::message>