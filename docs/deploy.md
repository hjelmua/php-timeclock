# Deploy till arnor.pentarex.se

Webbfilerna ägs av `nobody:nogroup` — direkt skrivning med `jonas` nekas.
Lösningen är att kopiera via `/tmp/` och sedan `sudo mv`.

## Deploya enskilda filer

```bash
# 1. Kopiera till /tmp på servern
scp fil1.php fil2.php jonas@arnor.pentarex.se:/tmp/

# 2. Flytta till webbmappen och sätt rätt ägare
ssh jonas@arnor.pentarex.se "sudo mv /tmp/fil1.php /tmp/fil2.php /var/www/web.hjelms.com/php-timeclock/ && sudo chown nobody:nogroup /var/www/web.hjelms.com/php-timeclock/fil1.php /var/www/web.hjelms.com/php-timeclock/fil2.php"
```

## Deploya alla filer (full deploy)

```bash
# 1. Rsync allt till /tmp/php-timeclock på servern
rsync -avz --omit-dir-times \
      --exclude='config/config.php' \
      --exclude='install.php' \
      --exclude='.git' \
      --exclude='.claude' \
      --exclude='docs/' \
  /Users/jonashjelm/Documents/Claude/php-timeclock/ \
  jonas@arnor.pentarex.se:/tmp/php-timeclock/

# 2. Flytta filerna och sätt rätt ägare
ssh jonas@arnor.pentarex.se "sudo cp -r /tmp/php-timeclock/. /var/www/web.hjelms.com/php-timeclock/ && sudo chown -R nobody:nogroup /var/www/web.hjelms.com/php-timeclock/ && rm -rf /tmp/php-timeclock"
```

## Vad som aldrig deployats

| Fil/mapp | Anledning |
|----------|-----------|
| `config/config.php` | Innehåller produktionens DB-uppgifter — finns redan på servern |
| `install.php` | Bara för ny installation, ska inte finnas i produktion |
| `.git/` | Git-metadata |
| `.claude/` | Lokala Claude-minnen |
| `docs/` | Dokumentation, inte webbkod |
