<?php

use Illuminate\Database\Eloquent\Model;
use Lacodix\LaravelScopedMailConfig\Concerns\HasMailConfig;
use Lacodix\LaravelScopedMailConfig\Exceptions\InvalidConfiguration;
use Lacodix\LaravelScopedMailConfig\Facades\ScopedMail;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Tests\AuthenticationTestUser;

it('resolves config from given closure', function () {
    $testScopeModel = TestScopeModel::create([
        'mail_config' => [
            'transport' => 'smtp',
            'host' => 'my.mail.server',
            'port' => 5876,
            'encryption' => 'tls',
            'username' => 'myusername',
            'password' => 'mypassword',
            'timeout' => 60,
            'local_domain' => null,
        ]
    ]);

    ScopedMail::resolveScopeUsing(fn () => $testScopeModel);

    $mailer = $this->app['mail.scoped.manager']->mailer('smtp');

    $transport = $mailer->getSymfonyTransport();

    $this->assertInstanceOf(EsmtpTransport::class, $transport);
    $this->assertSame('myusername', $transport->getUsername());
    $this->assertSame('mypassword', $transport->getPassword());
    $this->assertSame('my.mail.server', $transport->getStream()->getHost());
    $this->assertSame(5876, $transport->getStream()->getPort());
});

it('throws an exception on wrong scopes', function () {
    ScopedMail::resolveScopeUsing(fn () => new WrongScopeModel());
    $this->app['mail.scoped.manager']->mailer('smtp');
})->throws(InvalidConfiguration::class);

it('resolves config from authenticated users', function () {
    ScopedMail::resolveScopeUsing(null);

    $user = AuthenticationTestUser::create([
        'username' => 'dominikmueller',
        'email' => 'dominik@lacodix.de',
        'password' => bcrypt('password'),
        'is_active' => true,
        'mail_config' => [
            'transport' => 'smtp',
            'host' => 'db.mail.server',
            'port' => 123,
            'encryption' => 'tls',
            'username' => 'dbmailuser',
            'password' => 'dbmailpass',
            'timeout' => 60,
            'local_domain' => null,
        ]
    ]);

    $this->actingAs($user);

    $mailer = $this->app['mail.scoped.manager']->mailer('smtp');

    $transport = $mailer->getSymfonyTransport();

    $this->assertInstanceOf(EsmtpTransport::class, $transport);
    $this->assertSame('dbmailuser', $transport->getUsername());
    $this->assertSame('dbmailpass', $transport->getPassword());
    $this->assertSame('db.mail.server', $transport->getStream()->getHost());
    $this->assertSame(123, $transport->getStream()->getPort());
});

it('can iterate over multiple scope configs', function () {
    $model1 = TestScopeModel::create([
        'mail_config' => [
            'transport' => 'smtp',
            'host' => 'my.mail.server',
            'port' => 5876,
            'encryption' => 'tls',
            'username' => 'myusername',
            'password' => 'mypassword',
            'timeout' => 60,
            'local_domain' => null,
        ]
    ]);

    $model2 = TestScopeModel::create([
        'mail_config' => [
            'transport' => 'smtp',
            'host' => 'one.other.server',
            'port' => 123,
            'encryption' => null,
            'username' => 'othername',
            'password' => 'otherpass',
            'timeout' => 60,
            'local_domain' => null,
        ]
    ]);

    ScopedMail::resolveScopeUsing(fn () => TestScopeModel::find(TestScopeModel::$currentId));


    TestScopeModel::$currentId = $model1->id;

    $mailer = $this->app['mail.scoped.manager']->mailer('smtp');
    $transport = $mailer->getSymfonyTransport();
    $this->assertInstanceOf(EsmtpTransport::class, $transport);
    $this->assertSame('myusername', $transport->getUsername());
    $this->assertSame('mypassword', $transport->getPassword());
    $this->assertSame('my.mail.server', $transport->getStream()->getHost());
    $this->assertSame(5876, $transport->getStream()->getPort());

    TestScopeModel::$currentId = $model2->id;

    $mailer = $this->app['mail.scoped.manager']->mailer('smtp');
    $transport = $mailer->getSymfonyTransport();
    $this->assertInstanceOf(EsmtpTransport::class, $transport);
    $this->assertSame('othername', $transport->getUsername());
    $this->assertSame('otherpass', $transport->getPassword());
    $this->assertSame('one.other.server', $transport->getStream()->getHost());
    $this->assertSame(123, $transport->getStream()->getPort());
});

class TestScopeModel extends Model implements HasMailConfig
{
    protected $table = 'table';
    public $timestamps = false;

    protected $guarded = [];

    public static $currentId = null;

    protected $casts = [
        'mail_config' => 'array',
    ];

    public function getMailConfig(string $name): array
    {
        return $this->mail_config;
    }
}

class WrongScopeModel extends Model
{
    protected $table = 'table';
}
