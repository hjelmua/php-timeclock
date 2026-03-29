# PHP Timeclock — Setup & Fix Design

**Date:** 2026-03-29

## Mål

Hämta den uppdaterade versionen av php-timeclock från produktionsservern till en lokal arbetsmiljö, införa git-hantering med dolda DB-uppgifter, och fixa brutna admin-länkar.

## Fas 1: Filhämtning

Hämta alla filer från servern till lokal mapp via rsync:

```bash
rsync -avz --exclude='*.log' jonas@arnor.pentarex.se:/var/www/web.hjelms.com/php-timeclock/ \
  /Users/jonashjelm/Documents/Claude/php-timeclock/
```

- Testfiler som följer med granskas och läggs antingen i `.gitignore` eller tas bort
- Lokal mapp: `/Users/jonashjelm/Documents/Claude/php-timeclock/`

## Fas 2: Git-setup

```bash
git init
git remote add origin git@github.com:hjelmua/php-timeclock.git
```

**.gitignore:**
```
config/config.php
*.log
```

**config/config.example.php** skapas som en kopia av `config.php` med tomma platshållarvärden:
- DB_HOST, DB_USER, DB_PASSWORD, DB_NAME (och övriga inställningar)
- Committad till git som referens för ny installation

Initial commit med alla filer utom ignorerade.

## Fas 3: Fixa brutna admin-länkar

Filer att granska: `admin_add_employee.php`, `admin_dashboard.php`, `admin_edit_employee.php`, `admin_edit_punch.php`, `admin_edit_punches.php`, `admin_fix_punches.php`, `manage_users.php`

Process:
1. Kartlägg alla `<a href>` och inkluderingar i admin-filer
2. Jämför med faktiskt existerande filer
3. Fixa sönderlagda länkar så de pekar rätt
4. Ofullständiga destinationer: antingen stub-sida eller ta bort länken

Scope: Endast navigeringens korrekthet — ingen logik eller databaskod ändras i detta steg.

## Filer att ignorera i git

- `config/config.php` — innehåller privata DB-uppgifter
- `*.log` — loggfiler
- Testfiler identifieras vid granskning efter rsync

## Resultat

- Lokal kopia med all kod från arnor
- Git-repo med ren historik
- `config.example.php` dokumenterar konfigurationsstrukturen
- Admin-gränssnittet navigerbart utan brutna länkar
