<?php

namespace Lacodix\LaravelScopedMailConfig\Facades;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Testing\Fakes\MailFake;

class ScopedMail extends Mail
{
    protected static $cached = false;

    public static function fake(): MailFake
    {
        $actualMailManager = static::isFake()
            ? static::getFacadeRoot()->manager
            : static::getFacadeRoot();

        return tap(new MailFake($actualMailManager), static function ($fake): void {
            static::swap($fake);
        });
    }

    protected static function getFacadeAccessor(): string
    {
        return 'mail.scoped.manager';
    }
}
