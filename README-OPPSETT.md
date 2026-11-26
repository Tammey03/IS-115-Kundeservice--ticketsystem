# Lokalt oppsett av ticketsystemet

Denne guiden gjelder lokal kjøring i XAMPP, også for den som mottar prosjektet som ZIP. Bruk oppdiktede data ved testing.

## 1. Installer og start

- Installer XAMPP med Apache, MariaDB og PHP 8.2 eller nyere. Koden bruker PHP-utvidelsene `pdo_mysql`, `mbstring` og `fileinfo`. Kontroller dem med `php -m` hvis du bruker et annet PHP-oppsett. [XAMPP](https://www.apachefriends.org/download.html)
- Installer Composer og kjør `composer install` fra mappen der `composer.json` og `composer.lock` ligger. Dette installerer PHPMailer i `vendor/`. [Composer](https://getcomposer.org/doc/01-basic-usage.md)
- Installer Mailpit hvis du vil teste e-postvarsling eller «Glemt passord». [Mailpit](https://mailpit.axllent.org/docs/install/)
- Pakk ut eller klon prosjektet under XAMPPs `htdocs`. Vanlige stier er `C:\xampp\htdocs` på Windows og `/Applications/XAMPP/xamppfiles/htdocs` på macOS. Start Apache og MySQL i XAMPP.

## 2. Sett opp databasen

1. Åpne [phpMyAdmin](http://localhost/phpmyadmin/) og opprett en tom database, for eksempel `kundeservice_db`, med tegnsett `utf8mb4`.
2. Velg databasen i phpMyAdmin, åpne **Importer**, velg SQL-eksporten som følger med leveransen, og start importen. En full eksport inneholder både tabellene og dataene fra gruppas database. Hopp over `config/schema.sql` når du bruker en full eksport.
3. Kopier `config/database.example.php` til `config/database.php`. Sjekk at databasenavn, vert, port, brukernavn og passord stemmer med ditt lokale oppsett. Standardverdiene er `localhost`, `kundeservice_db`, port 3306 og `root` med tomt passord.

Hvis du vil starte med tomme tabeller, kan du importere [config/schema.sql](config/schema.sql) i stedet for gruppeeksporten. Skjemaet oppretter seks tomme tabeller og inkluderer begge migreringene.

**Ved import av en eldre database**

- Sjekk om `ticket_attachments.attachment_id` finnes. Hvis kolonnen mangler i den eldre tabellen, kjør [001_multiple_ticket_attachments.sql](config/migrations/001_multiple_ticket_attachments.sql).
- Sjekk om `users.password_reset_hash` og `users.password_reset_expires_at` finnes. Hvis begge mangler, kjør [002_password_reset.sql](config/migrations/002_password_reset.sql). Kjør hver relevant migrering én gang.

## 3. Logg inn

Åpne [innloggingssiden](http://localhost/IS-115-Kundeservice--ticketsystem/pages/login.php). Tilpass mappenavnet i URL-en hvis du har pakket ut prosjektet under et annet navn.

Bruk testkontoene som følger med gruppas databaseeksport. Gruppen legger ved innlogging for rollene sensor skal teste. Generell chat krever at bruker-ID 5 tilhører en saksbehandler. Nye kontoer opprettet via **Opprett bruker** får rollen kunde.

## 4. Start lokal e-post og test

Kopier `config/mail.example.php` til `config/mail.local.php`. Eksemplet sender lokal SMTP til port 1025 uten innlogging. Start Mailpit i en terminal.

```sh
mailpit --listen 127.0.0.1:8025 --smtp 127.0.0.1:1025
```

Åpne [Mailpit-innboksen](http://127.0.0.1:8025) i en egen fane. Varslene vises der selv med oppdiktede adresser.

| Funksjon | Slik sjekker du den |
| --- | --- |
| Registrering, innlogging og utlogging | Logg inn med kunden, åpne **Min profil**, og sjekk at **Logg ut** tar deg tilbake til innlogging. |
| Saksoversikt, kategori og prioritet | Opprett en sak som kunde og sjekk **Mine saker**. Åpne sakslisten som admin, velg **Ta sak**, og oppdater status og prioritet. |
| Vedlegg | Legg ved en liten PDF eller et bilde ved opprettelse. Åpne filen fra saksoversikten. Grensen i appen er fem filer på inntil 5 MB per fil. |
| Saksprat og generell chat | Skriv som kunde og svar som tildelt saksbehandler. Sjekk at historikken vises, og at begge mottakere får varsler i Mailpit. |
| Profil og passord | Rediger profilen som kunde eller saksbehandler. Bytt passord, sjekk varselet og test ny innlogging. |
| Glemt passord | Fra innlogging, be om en lenke for testkontoens adresse. Åpne den nyeste lenken i Mailpit, lagre nytt passord og logg inn. Lenken kan brukes én gang og utløper etter 30 minutter. |

For passordlenker brukes `http://localhost` med XAMPPs standardport. Ved annen adresse eller port setter du miljøvariabelen `PASSWORD_RESET_URL` til den fullstendige URL-en til `pages/glemtPassord.php`.

## Funn fra gjennomgangen

- **Filnavn med forskjellig bruk av store og små bokstaver.** `pages/bruker/mineSaker.php`, `pages/admin/alleSaker.php` og `pages/admin/behandleSak.php` laster `includes/ticketLabels.php`, mens filen heter `ticketlabels.php`. På et filsystem som skiller mellom store og små bokstaver, må disse tre innlastingene bruke samme navn som filen før de sidene kan åpnes.
- **Generell chat har fast mottaker.** Uten en aktiv admin med bruker-ID 5 stopper opprettelsen av en ny generell samtale. Saksprat bruker saksbehandleren som er tildelt saken.
- **Superadmin er delvis implementert.** Rollen kan logge inn og åpne sin profil. Oversikt, redigering og sletting av brukere står som kommende funksjonalitet.
- **E-post ved tildeling kan sendes før databasen bekrefter endringen.** `taTicket()` lagrer den automatiske chatmeldingen inne i transaksjonen. Det betyr at e-postvarselet kan være sendt hvis lagringen av tildelingen senere feiler.
- **Leveransen trenger et grunnskjema.** Migreringene i `config/migrations/` endrer eksisterende tabeller. Importer [config/schema.sql](config/schema.sql) for en ny database.

## Levering som ZIP

Ta med kildekoden, begge README-filene, `composer.json`, `composer.lock`, migreringene, tabellskjemaet og eksempelfilene i `config/`. Legg også ved gruppas SQL-eksport og innloggingsopplysningene til testkontoene. Utelat `.git/`, `.DS_Store`, `config/database.php`, `config/mail.local.php`, `config/test-accounts.php` og `vendor/`. For en ZIP som skal kunne kjøres uten Composer, må den installerte `vendor/`-mappen følge med. Eventuelle SQL-eksporter med testdata bør bare inneholde oppdiktede kontoer, saker og meldinger.
