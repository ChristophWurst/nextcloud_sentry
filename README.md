# Nextcloud Sentry Integration

A Sentry integration that sends unhandled exceptions to a Sentry instance to aggregate application crashes. You either have to set up your own Sentry instance or use your sentry.io account. See the admin documentation for how to configure this app.

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

### Minimum log level

The setting `sentry.minimum.log.level` can be used to filter out exceptions with lower log level before they are sent to the Sentry instance. By default `warning` and upwards will be captured.

To change this you can specify the minimum log level of the Sentry instance by following command

``` bash
php occ config:system:set sentry.minimum.log.level --value=0 --type=integer
```

or add the entry directly to `config/config.php`

```
  "sentry.minimum.log.level" => 0
```

The value needs to be a number between 0 and 4, where 0 is debug, 1 is info, 2 is warning, 3 is error and 4 is fatal.
