<?php

namespace Lacodix\LaravelScopedMailConfig;

use Illuminate\Mail\MailManager;
use Lacodix\LaravelScopedMailConfig\Concerns\HasMailConfig;
use Lacodix\LaravelScopedMailConfig\Exceptions\InvalidConfiguration;

class ScopedMailManager extends MailManager
{
    /**
     * The default scope resolver.
     *
     * @var (callable(): mixed)|null
     */
    protected static $defaultScopeResolver;

    public static function resolveScopeUsing(null|callable $resolver): void
    {
        static::$defaultScopeResolver = $resolver;
    }

    protected function getConfig(string $name): array
    {
        $scope = $this->defaultScopeResolver();

        if (! $scope instanceof HasMailConfig) {
            throw new InvalidConfiguration("Scope object doesn't implement 'HasMailConfig' interface.");
        }

        return $scope->getMailConfig($name);
    }

    protected function defaultScopeResolver()
    {
        if (static::$defaultScopeResolver !== null) {
            return (static::$defaultScopeResolver)();
        }

        return $this->app['auth']->guard()->user();
    }
}
