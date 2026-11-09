# Admin documentation

## Configuration

This app needs a valid Sentry DSN to operate. From the "Client Keys" tab in your Sentry project, use the "DSN" as `sentry.public-dsn`. This single DSN is used for both server-side and client-side (browser) error reporting. You can set it via the command line:

```bash
php occ config:system:set sentry.public-dsn --value=https://xxxxx@sentry.io/1234567
```

or add the entry directly to `config/config.php`

```php
  "sentry.public-dsn" => "https://xxxxx@sentry.io/1234567",
```

### Deprecated `sentry.dsn`

Older versions of this app also required a separate `sentry.dsn` config, using the "DSN (Legacy)" value that included a secret key. This is no longer needed: as of Sentry 9, the secret key is not required and this app now only uses `sentry.public-dsn`. If you still have `sentry.dsn` configured, it continues to work as a fallback (its secret is stripped automatically before use), but you should remove it and rely solely on `sentry.public-dsn`.

### Sampling rate

This app can monitor Nextcloud's performance. The sampling rate is derived from the log level (more verbose -> more sampling) but can be overwritten:

```php
  "sentry.sampling-rate" => 0.1,
```

Read more at https://docs.sentry.io/platforms/php/guides/symfony/performance/.

### CSP error reporting

[Sentry can capture CSP violation reports](https://docs.sentry.io/product/security-policy-reporting/). Just set the `sentry.csp-report-url` in addition to your other configuration parameters:

```
  "sentry.public-dsn" => "https://xxxxx@sentry.io/1234567",
  "sentry.csp-report-url" => "https://sentry.io/api/1234567/security/?sentry_key=adf205d197bd7201da1d564379e694a2",
```

### Preventing Abuse

[It is recommended](https://docs.sentry.io/clients/javascript/usage/#preventing-abuse) to whitelist
known hosts (your Nextcloud host) to prevent malicious reports.


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
