# laravel-scoped-mail-config

[![Latest Version on Packagist](https://img.shields.io/packagist/v/lacodix/laravel-scoped-mail-config.svg?style=flat-square)](https://packagist.org/packages/lacodix/laravel-scoped-mail-config)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/lacodix/laravel-scoped-mail-config/test.yaml?branch=master&label=tests&style=flat-square)](https://github.com/lacodix/laravel-scoped-mail-config/actions?query=workflow%3Atest+branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/lacodix/laravel-scoped-mail-config/style.yaml?branch=master&label=code%20style&style=flat-square)](https://github.com/lacodix/laravel-scoped-mail-config/actions?query=workflow%3Astyle+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/lacodix/laravel-scoped-mail-config.svg?style=flat-square)](https://packagist.org/packages/lacodix/laravel-scoped-mail-config)

This package makes it an ease to send mails with dynamic mail configuration. The mail configuration
can be provided by any model or class, that implements our HasMailConfig Interface.

With this in mind it is the perfect fit for multi tenancy packages like
[Laravel-multitenancy](https://github.com/spatie/laravel-multitenancy) by spatie.

It also offers the possibility to keep mail configs in user or team models or even if you just want to be able
to offer your admin-user to set a dynamic mail configuration.

## Documentation

You can find the entire documentation for this package on [our documentation site](https://www.lacodix.de/docs/laravel-scoped-mail-config)

## Installation

```bash
composer require lacodix/laravel-scoped-mail-config
```

## Basic Usage

Just add our interface to your scope model

```php
use Illuminate\Database\Eloquent\Model;
use Lacodix\LaravelScopedMailConfig\Concerns\HasMailConfig;

class Tenant extends Model implements HasMailConfig
{
    public function getMailConfig($name): array {
        return [
            'transport' => 'smtp',
            'host' => 'my.smtp.server',
            'port' => 587,
            'encryption' => null,
            'username' => 'my@email.login',
            'password' => 'mypassword',
            'timeout' => 60,
            'local_domain' => null,
            'from' => [
                'address' => 'my@email.login',
                'name' => 'myname',
            ],
        ];
    }
}
```

In your AppServiceProvider you now just need to inform the package how it shall resolve the mail configuration.
By default the Package resolves the current authenticated user. So if you want to use user specific mail configuration
then just skip this step.

```
use Lacodix\LaravelScopedMailConfig\Facades\ScopedMail;

public function register(): void
{
    ScopedMail::resolveScopeUsing(fn () => Tenant::getCurrentTenant());
}
```

Then you can send mails with this dynamic configuration just by using our facade like laravel Mail facade:
```
ScopedMail::to('my@email.de')->send($mailable);
```
ScopedMail is just extending standard Mail facade, so there are all functionalities, that you already know from
laravel [Mail](https://laravel.com/docs/mail)

Finally it is also possible to use this facade in tests
```
ScopedMail::fake();
```

## Testing

```bash
composer test
```

## Contributing

Please run the following commands and solve potential problems before committing
and think about adding tests for new functionality.

```bash
composer rector:test
composer insights
composer csfixer:test
composer phpstan:test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [lacodix](https://github.com/lacodix)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
