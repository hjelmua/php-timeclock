# PHP Timeclock

Enkel tidsstämpling för personal — stämpla in och ut via webbläsaren.

## Funktioner

- In/ut-stämpling för anställda
- Admin-panel för hantering av personal, grupper och kontor
- Rapporter och exportfunktion
- Audit-logg för redigerade stämplingar
- Rollbaserad åtkomst (admin, reports, time_admin)

## Installation

### 1. Databas

Skapa en MySQL-databas och importera schemat:

```bash
mysql -u <user> -p <databasnamn> < docs/database-schema.sql
```

Eller kör `install.php` i webbläsaren (ta bort filen efter installation).

### 2. Konfiguration

```bash
cp config/config.example.php config/config.php
```

Fyll i DB-uppgifter och timezone i `config/config.php`.

### 3. Åtkomst

| URL | Beskrivning |
|-----|-------------|
| `timeclock.php` | Stämpling för personal |
| `admin_login.php` | Admin-inloggning |

## Deploy till produktion

Se [docs/deploy.md](docs/deploy.md) för instruktioner.

## Krav

- PHP med MySQLi
- MySQL/MariaDB
