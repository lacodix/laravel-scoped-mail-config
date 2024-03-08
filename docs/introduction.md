---
title: Introduction
weight: 1
---

This package makes it an ease to send mails with dynamic mail configuration. The mail configuration 
can be provided by any model or class, that implements our HasMailConfig Interface.

With this in mind it is the perfect fit for multi tenancy packages like 
[Laravel-multitenancy](https://github.com/spatie/laravel-multitenancy) by spatie.

It also offers the possibility to keep mail configs in user or team models or even if you just want to be able
to offer your admin-user to set a dynamic mail configuration.

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
