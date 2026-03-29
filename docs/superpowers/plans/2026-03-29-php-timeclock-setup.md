# PHP Timeclock Setup Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Hämta php-timeclock från produktionsservern till lokal miljö, sätta upp git med dolda DB-uppgifter, och fixa brutna admin-länkar.

**Architecture:** Rsync från arnor.pentarex.se → lokal mapp → git init med .gitignore för config/config.php → granska och rensa testfiler → fixa navigeringslänkar i admin-sidor.

**Tech Stack:** PHP, MySQL, bash/rsync, git

---

## Filstruktur

Befintliga filer (hämtas från server):
- `config/config.php` — DB-uppgifter (ignoreras i git)
- `config/config.example.php` — **skapas** som platshållare för git
- `admin_*.php` — admin-gränssnitt (fixas)
- `timeclock.php` — huvud stämplingsvy
- `manage_users.php`, `install.php`, `upgrade.php`, `upgrade_cli.php` — övriga sidor
- `.gitignore` — **skapas**
- `docs/` — spec och plan (redan skapad)

---

### Task 1: Hämta filer från servern med rsync

**Files:**
- Skapar: allt i `/Users/jonashjelm/Documents/Claude/php-timeclock/`

- [ ] **Steg 1: Kör rsync**

```bash
rsync -avz --exclude='*.log' \
  jonas@arnor.pentarex.se:/var/www/web.hjelms.com/php-timeclock/ \
  /Users/jonashjelm/Documents/Claude/php-timeclock/
```

Förväntat: Lista med överförda filer, avslutning med "sent X bytes".

- [ ] **Steg 2: Verifiera att filer kom med**

```bash
ls /Users/jonashjelm/Documents/Claude/php-timeclock/
```

Förväntat: `timeclock.php`, `admin_dashboard.php`, `config/` och övriga PHP-filer syns.

---

### Task 2: Granska och hantera testfiler

**Files:**
- Modify/Delete: testfiler identifierade i projektmappen

- [ ] **Steg 1: Lista alla filer för granskning**

```bash
find /Users/jonashjelm/Documents/Claude/php-timeclock/ -type f | sort
```

Notera alla filer som ser ut som test/temporära filer (t.ex. `test_*.php`, `tmp_*`, `debug_*`, `old_*`).

- [ ] **Steg 2: Besluta om varje testfil**

För varje testfil, välj ett av:
- Ta bort: `rm <fil>` om den inte behövs alls
- Lägg i .gitignore: om den behövs lokalt men inte i git

Exempelformat för .gitignore-rader (läggs till i Task 3):
```
test_*.php
debug_*.php
```

---

### Task 3: Sätt upp git och .gitignore

**Files:**
- Skapa: `.gitignore`
- Kör: `git init`

- [ ] **Steg 1: Initiera git-repo**

```bash
cd /Users/jonashjelm/Documents/Claude/php-timeclock
git init
```

Förväntat: "Initialized empty Git repository in .../php-timeclock/.git/"

- [ ] **Steg 2: Skapa .gitignore**

Skapa filen `/Users/jonashjelm/Documents/Claude/php-timeclock/.gitignore` med innehåll:

```
# Privat konfiguration med DB-uppgifter
config/config.php

# Loggfiler
*.log

# Testfiler (lägg till fler om Task 2 identifierade några)
# test_*.php
```

- [ ] **Steg 3: Koppla remote**

```bash
git remote add origin git@github.com:hjelmua/php-timeclock.git
```

Förväntat: Inget fel.

---

### Task 4: Skapa config.example.php

**Files:**
- Skapa: `config/config.example.php`

- [ ] **Steg 1: Läs config/config.php för att se vilka variabler som finns**

```bash
cat /Users/jonashjelm/Documents/Claude/php-timeclock/config/config.php
```

Notera alla variabler/konstanter som definieras (DB-host, user, password, dbname, timezone m.m.).

- [ ] **Steg 2: Skapa config.example.php med tomma värden**

Skapa `config/config.example.php` baserat på vad du såg i steget ovan, men med tomma strängvärden. Exempel (anpassa efter faktiska variabler):

```php
<?php
// Kopiera den här filen till config.php och fyll i dina värden

define('DB_HOST', '');       // t.ex. 'localhost'
define('DB_USER', '');       // databasanvändare
define('DB_PASS', '');       // databaslösenord
define('DB_NAME', '');       // databasnamn
define('TIMEZONE', '');      // t.ex. 'Europe/Stockholm'
// Lägg till övriga inställningar från config.php här
```

- [ ] **Steg 3: Verifiera att config.php är ignorerad men config.example.php inte är det**

```bash
git status
```

Förväntat: `config/config.example.php` visas som "untracked" (kan committas). `config/config.php` ska INTE synas.

---

### Task 5: Initial commit

**Files:**
- Alla spårbara filer

- [ ] **Steg 1: Lägg till alla filer**

```bash
git add .
git status
```

Kontrollera att `config/config.php` INTE är med i listan. Avbryt om den syns — lägg till den i .gitignore och börja om.

- [ ] **Steg 2: Skapa initial commit**

```bash
git commit -m "$(cat <<'EOF'
Initial commit: php-timeclock från arnor.pentarex.se

Importerar befintlig kodbas från produktionsserver.
Privat config (config/config.php) ignoreras via .gitignore.
EOF
)"
```

Förväntat: "[main (root-commit) xxxxxxx] Initial commit..."

---

### Task 6: Kartlägg och fixa brutna admin-länkar

**Files:**
- Modify: `admin_dashboard.php`, `admin_add_employee.php`, `admin_edit_employee.php`, `admin_edit_punch.php`, `admin_edit_punches.php`, `admin_fix_punches.php`, `manage_users.php`

- [ ] **Steg 1: Lista alla PHP-filer som faktiskt finns**

```bash
ls /Users/jonashjelm/Documents/Claude/php-timeclock/*.php
```

Spara listan — det är de filer som faktiskt existerar.

- [ ] **Steg 2: Extrahera alla href-länkar i admin-filer**

```bash
grep -rn 'href=' /Users/jonashjelm/Documents/Claude/php-timeclock/admin_*.php \
  /Users/jonashjelm/Documents/Claude/php-timeclock/manage_users.php | grep '\.php'
```

Jämför varje länkad `.php`-fil mot listan från Steg 1. Notera alla som pekar på filer som INTE finns.

- [ ] **Steg 3: Extrahera alla include/require i admin-filer**

```bash
grep -rn 'include\|require' /Users/jonashjelm/Documents/Claude/php-timeclock/admin_*.php
```

Kontrollera att inkluderade filer existerar.

- [ ] **Steg 4: Fixa varje bruten länk**

För varje bruten länk, välj ett av:
- **Korrigera sökvägen** om filen finns men länken är fel
- **Ta bort länken** om destinationssidan saknas och inte ska byggas nu
- **Ersätt med stub** om länken ska finnas men sidan är ofullständig:

```php
<?php
// Lägg till denna stub-kommentar i en befintlig sida, eller skapa en ny:
echo '<p>Den här funktionen är inte implementerad ännu.</p>';
echo '<a href="admin_dashboard.php">Tillbaka till dashboard</a>';
```

Gör en fil i taget. Testa i webbläsare om möjligt efter varje fix.

- [ ] **Steg 5: Commit fixade länkar**

```bash
git add admin_*.php manage_users.php
git commit -m "$(cat <<'EOF'
fix: rätta brutna admin-navigeringslänkar

Korrigerar href-referenser och tar bort/stubbar
länkar till ej implementerade sidor.
EOF
)"
```

---

### Task 7: Pusha till GitHub

**Files:**
- Remote: `git@github.com:hjelmua/php-timeclock.git`

- [ ] **Steg 1: Kontrollera remote**

```bash
git remote -v
```

Förväntat: `origin git@github.com:hjelmua/php-timeclock.git (fetch/push)`

- [ ] **Steg 2: Pusha**

```bash
git push -u origin main
```

Förväntat: "Branch 'main' set up to track remote branch 'main' from 'origin'."

> **OBS:** GitHub-repot har 29 befintliga commits på `main`. Pushen kan misslyckas med "rejected, non-fast-forward". Om det händer: diskutera med användaren — force push skriver över gammal historik.

---

## Verifiering

Efter alla tasks:
- [ ] `git log --oneline` visar minst 2 commits (initial + länkfix)
- [ ] `git status` visar "nothing to commit"
- [ ] `config/config.php` syns INTE på GitHub
- [ ] `config/config.example.php` syns på GitHub
- [ ] `admin_dashboard.php` kan öppnas lokalt utan PHP-fel (kräver lokal PHP/webbserver)
