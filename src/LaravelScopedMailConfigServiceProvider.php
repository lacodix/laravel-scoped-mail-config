<?php

namespace Lacodix\LaravelScopedMailConfig;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelScopedMailConfigServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('laravel-scoped-mail-config');
    }

    public function register(): void
    {
        $this->app->bind('mail.scoped.manager', static fn ($app) => new ScopedMailManager($app));

        parent::register();
    }
}
