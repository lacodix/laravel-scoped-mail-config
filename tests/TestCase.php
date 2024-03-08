<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Lacodix\LaravelScopedMailConfig\Concerns\HasMailConfig;
use Lacodix\LaravelScopedMailConfig\LaravelScopedMailConfigServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use LazilyRefreshDatabase;
    use InteractsWithViews;

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected function getPackageProviders($app)
    {
        return [
            LaravelScopedMailConfigServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->string('username');
            $table->string('password');
            $table->string('remember_token')->default(null)->nullable();
            $table->tinyInteger('is_active')->default(0);
            $table->json('mail_config');
        });

        Schema::create('table', function (Blueprint $table) {
            $table->increments('id');
            $table->json('mail_config');
        });
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('auth.providers.users.model', AuthenticationTestUser::class);

        $app['config']->set('auth.guards.api', [
            'driver' => 'token',
            'provider' => 'users',
            'hash' => false,
        ]);
    }
}

class AuthenticationTestUser extends Authenticatable implements HasMailConfig
{
    public $table = 'users';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var string[]
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'mail_config' => 'array',
    ];

    public function getMailConfig(string $name): array
    {
        return $this->mail_config;
    }
}
