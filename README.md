# Nextcloud Sentry Integration
Sentry integration for Nextcloud

![Sentry screenshot](https://user-images.githubusercontent.com/1374172/32739917-8f719584-c8a1-11e7-9f06-182043c2b970.png)

## Configuration

All this app needs to operate is a valid Sentry DSN. You can set it via the command line

```bash
php occ config:system:set sentry.dsn --value=https://xxxxx:yyyyy@sentry.io/1234567
```

or add the entry directly to `config/config.php`

```
  "sentry.dsn" => "https://xxxxx:yyyyy@sentry.io/1234567"
```
