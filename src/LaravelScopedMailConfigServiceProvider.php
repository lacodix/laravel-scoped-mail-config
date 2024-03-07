<?php

namespace Lacodix\LaravelScopedMailConfig;

use Illuminate\Database\Eloquent\Builder;
use Lacodix\LaravelScopedMailConfig\Scopes\OrScope;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelScopedMailConfigServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('laravel-scoped-mail-config');
    }

    public function boot(): void
    {
        parent::boot();

        Builder::macro(
            'withGlobalOrScopes',
            // @phpstan-ignore-next-line
            fn (array $scopes = null) => $this->withGlobalScope(md5(serialize($scopes)), new OrScope($scopes))
        );
    }
}
