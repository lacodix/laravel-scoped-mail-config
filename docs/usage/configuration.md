---
title: Configuration
weight: 1
---

## Prepare your model

In order to use dynamic mail configuration for sending mails, you have to add our interface to your model. This can
either be the User model, a Tenant model of any multi-tenancy-system, team-models and much more. You can even use it
with a singleton setting model like Spaties [Laravel settings](https://github.com/spatie/laravel-settings).

```
use Illuminate\Database\Eloquent\Model;
use Lacodix\LaravelScopedMailConfig\Concerns\HasMailConfig;

class MyModel extends Model implements HasMailConfig
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

The getMailConfig method must return a valid mail configuration with a valid transport driver. You can use any
settings that you are used to know from [laravel mail functionality](https://laravel.com/docs/10.x/mail#configuration).
Usually it is kind of an smtp setting, but for sure you can also use mailgun, ses and other drivers. Your model just 
needs to return a valid configuration.

It is also possible to use different drivers per model (see sending mails).

To receive the data from your database, you could just add a json field to your users model:
```
use Illuminate\Database\Eloquent\Model;
use Lacodix\LaravelScopedMailConfig\Concerns\HasMailConfig;

class MyModel extends Model implements HasMailConfig
{
    protected $casts = [
        'mail_config' => 'array',
    ];

    public function getMailConfig(string $name): array
    {
        return $this->mail_config;
    }
}
```

## Prepare the resolver

If you just want to use the current authenticated user for resolving a dynamic mail configuration, you can skip this step.
If you want to use other models, you have to inform the package how to resolve the scope model. You can do this for
example in your AppServiceProvider

```
use Lacodix\LaravelScopedMailConfig\Facades\ScopedMail;

ScopedMail::resolveScopeUsing(fn () => MyModel::resolveTheCurrentOne());
```

for Spatie multi-tenancy package this would look like this 
```
use Lacodix\LaravelScopedMailConfig\Facades\ScopedMail;

ScopedMail::resolveScopeUsing(fn () => Tenant::current());
```

If you need to use this package without having multiple configurations for users and tenants, and you just want to 
use a dynamic configuration from database, that might be managed with packages like spatie 
[laravel-settings](https://github.com/spatie/laravel-settings), then do it that way:
```
use Lacodix\LaravelScopedMailConfig\Facades\ScopedMail;

ScopedMail::resolveScopeUsing(fn () => app()->make(GeneralSettings::class));
```
in this example it will resolve always to the same Instance, but keep in mind that ScopedMail isn't bound to your app
as singleton instance, so it will always do this resolving, even if it is called multiple times.
