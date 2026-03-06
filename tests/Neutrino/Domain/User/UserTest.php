<?php

declare(strict_types=1);

namespace NeutrinoTest\Domain\User;

use Mezzio\Authentication\UserInterface;
use Neutrino\Domain\Account\Account;
use Neutrino\Domain\Account\AccountMembership;
use Neutrino\Domain\User\Email;
use Neutrino\Domain\User\Password;
use Neutrino\Domain\User\Role;
use Neutrino\Domain\User\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(User::class)]
class UserTest extends TestCase
{
    #[Test]
    #[Group('User')]
    public function itCanBeCreatedWithValidArguments(): void
    {
        $user = new User(
            new Email('vasildakov@gmail.com'),
            new Password('password')
        );

        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertEmpty($user->getRoles());
    }

    public function itShouldNotBeCreatedWithInvalidEmail(): void {}

    public function itShouldNotBeCreatedWithInvalidPassword(): void {}

    #[Test]
    #[Group('User')]
    public function itCanAddAndRetrieveRoles(): void
    {
        $user = $this->user();

        $member = new Role('manager', Role::SCOPE_PLATFORM);
        $admin = new Role('administrator', Role::SCOPE_PLATFORM);
        $user->addRole($member);
        $user->addRole($admin);

        $this->assertNotEmpty($user->getRoles());
        $this->assertContains($member, $user->getRoles());
        $this->assertContains($admin, $user->getRoles());
    }


    #[Test]
    #[Group('User')]
    public function itCanRemoveRoles(): void
    {
        $user = $this->user();

        $member = new Role('manager', Role::SCOPE_PLATFORM);
        $user->addRole($member);
        $user->removeRole($member);
        $this->assertEmpty($user->getRoles());
    }

    #[Test]
    #[Group('User')]
    public function itCanGetAccountsFromMemberships(): void
    {
        $user = $this->user();
        $accountA = new Account('Acme');
        $accountB = new Account('Beta');

        $user->addAccountMembership(AccountMembership::owner($accountA, $user));
        $user->addAccountMembership(AccountMembership::owner($accountB, $user));

        $accounts = $user->getAccounts();

        $this->assertCount(2, $accounts);
        $this->assertContains($accountA, $accounts);
        $this->assertContains($accountB, $accounts);
    }

    private function user(): User
    {
        return new User(
            new Email('vasildakov@gmail.com'),
            new Password('password')
        );
    }

}
