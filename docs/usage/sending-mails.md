---
title: Sending Mails
weight: 2
---

After configuration and preparing the resolver, you can just use our ScopedMail facade like you would do it with
the laravel Mail facade.

## Sending Mails

```php
use Lacodix\LaravelScopedMailConfig\Facades\ScopedMail;

ScopedMail::to($request->user())->send(new OrderShipped());
```

## Use multiple drivers

Per default the ScopedMail facade will always select the default driver of your laravel configuration. Usualy
this is defined in your env variable MAIL_MAILER.

But this isn't important if you configured the package like we showed up in our configuration examples, because 
the `getMailConfig()` method always returned the final settings including the driver.

Sometimes it might be necessary to keep multiple configurations per model - maybe you want do be able to use 
two different smtp servers for different purposes. 

Then you have to extend the `getMailConfig()` method to take in account the mail driver name.

```
use Illuminate\Database\Eloquent\Model;
use Lacodix\LaravelScopedMailConfig\Concerns\HasMailConfig;

class MyModel extends Model implements HasMailConfig
{
    public function getMailConfig($name): array {
        $config = [
            'smtp' => [
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
            ],
            'smtp2' => [
                'transport' => 'smtp',
                'host' => 'smtp.mailgun.com',
                'port' => 587,
                'encryption' => null,
                'username' => 'mailgunuser',
                'password' => 'mailgunpass',
                'timeout' => 60,
                'local_domain' => null,
                'from' => [
                    'address' => 'my@mailgun.sender',
                    'name' => 'myname',
                ],
            ],
        ];
    }
}
```

It is also possible to use the standard ses and mailgun drivers, but but
since this configurations aren't handled by mail-config, it will be always the same service.
So to use mailgun and other mailservices with this package, use the smtp configuration of this services.

After you set up multiple configs, you can just use it
```
ScopedMail::driver('smtp')->to($request->user())->send(new OrderShipped());
ScopedMail::driver('smtp2')->to($request->user())->send(new OrderShipped());

// This one uses the driver that is set up in your config('mail.default') - usually env var MAIL_MAILER 
ScopedMail::to($request->user())->send(new OrderShipped()); 
```

## Use the scoped config for core laravel functions

Even when you use ScopedMail for all your manual mails, laravel core is still using the default 
driver for functionalities like notifications. This is e.g. used in ResetPassword mails

This might be your desired behaviour if you want to use a base mail configuration for all 
core functionality and ScopedMail only for dedicated jobs, but sometimes you also need to 
send core mails over ScopedMail - maybe if you set your settings via spatie settings package.

In this cases you can just override the binding of the default mail manager. Just add the
following lines to your AppServiceProvider boot method.

```php
public function boot(): void
{
    ...
    
    $this->app->bind(
        \Illuminate\Contracts\Mail\Factory::class, 
        static fn ($app) => new \Lacodix\LaravelScopedMailConfig\($app)
    );
}
```

