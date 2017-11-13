# nextcloud_sentry
Sentry integration for Nextcloud

## Configuration

All this app needs to operate is a valid Sentry DSN. You can set it via the command line

```bash
bash php occ config:system:set sentry.dsn --value=https://xxxxx:yyyyy@sentry.io/1234567
```

or add the entry directly to `config/config.php`

```
  "sentry.dsn" => "https://xxxxx:yyyyy@sentry.io/1234567"
```
