# IS-115-Kundeservice--ticketsystem
Oppgaven 
Bedriften mottar ofte kundehenvendeser via e-post og kontaktskjema, noe som kan gjøre det vanskelig å holde oversitk over saker og oppføging. 
I dette prosjektet skal dere utvikle et webbasert kundeservice- og ticketsystem hvor kunder kan sende inn henvendelser, mens ansatte kan behandle, prioritere og svare på sakene gjennom et administrasjons panel. 
Systemet skal gi oversikt over status på saker, hvem som behandler dem, og historikk over kommunikasjon. 
Løsningen skal utvikles i PHP med ev. bruk av HTML, CSS og JavaScript hvis nødvendig. 

Prosjektet bør innhold:
- opprettelse av saker/tickets
- brukerhåndtering
- status på saker (åpen, lukket osv.)
- kommentar- eller meldingssystem
- filopplasting
- databaseintegrasjon
- ev. e-postvarsling (enkelt nivå)

Utover detter er opp til dere å deifnere systemests funkasjonalitet og omfang 

Dere oppfordres til å tenkte på:
- brukervennlighet
- struktur og kodekvelitet
- sikkerhet
- videreutvikling og vedlikehold
- samarbeid og arbeidsflyt i team

Dere står fritt til å velge struktur, design g hvilken funksjoner dere ønsker å prioritere.
Dere må selv vurdere hvilke behov brukeren har, og hvordan systemet bør bygges for å gi en god brukeropplevelse
Prosjekt skal vise at dere kan planlegge, utvikle og dokumentere en webapplikasjon.
Det forventes at dere gjør egen tekninske vurdering og begrunne valgene dere tar underveis. 

## Flere vedlegg per sak

Kjør `config/migrations/001_multiple_ticket_attachments.sql` én gang i en eksisterende database før du tar i bruk filopplasting med flere vedlegg. Migreringen beholder filene som allerede er lagret.

## E-postvarsling

Nye meldinger i saksprat og generell chat varsles på e-post til den andre deltakeren. Kunden får varsel når saksbehandleren svarer, og tildelt saksbehandler får varsel når kunden skriver. Ved passordendring fra «Min profil» får kontoeieren et varsel.

1. Kjør `composer install` i prosjektmappen for å installere PHPMailer.
2. Kopier `config/mail.example.php` til `config/mail.local.php`, som Git ignorerer.
3. Installer [Mailpit](https://mailpit.axllent.org/docs/install/) og start det med `mailpit --listen 127.0.0.1:8025 --smtp 127.0.0.1:1025`.
4. Åpne [Mailpit-innboksen](http://127.0.0.1:8025). Bruk testadresser med `@example.com`. Svar på en chatmelding som saksbehandler eller endre passordet på en testkonto for å se varslene.

Mailpit tar imot e-posten lokalt. Demoen bruker ingen innlogging hos en e-postleverandør.

Varsler sendes etter at meldingen eller passordendringen er lagret. E-posten inneholder kun et varsel. Ved SMTP-feil blir endringen stående, og feilen logges i PHPs feillogg. Uten lokal e-postkonfigurasjon er varsling deaktivert.

## Glemt passord

Kjør `config/migrations/002_password_reset.sql` én gang i databasen. Start Mailpit og åpne «Glemt passord?» fra innloggingssiden. Oppgi e-postadressen til en testkonto og åpne lenken i Mailpit. Lenken kan brukes én gang og utløper etter 30 minutter. Det går minst ett minutt mellom utsendinger til samme konto.

Lenken bruker XAMPP-adressen på `http://localhost` som standard. Hvis prosjektet kjøres på en annen adresse eller port, sett miljøvariabelen `PASSWORD_RESET_URL` til den fullstendige URL-en til `pages/glemtPassord.php`.
