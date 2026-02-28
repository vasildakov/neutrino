<?php

declare(strict_types=1);

namespace Neutrino\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Neutrino\Domain\Account\Account;
use Neutrino\Domain\Account\AccountMembership;
use Neutrino\Domain\Account\AccountRole;
use Neutrino\Domain\Store\RandomSlugGenerator;
use Neutrino\Domain\Store\Store;
use Neutrino\Domain\Store\StoreSlug;
use Neutrino\Domain\User\Email;
use Neutrino\Domain\User\Password;
use Neutrino\Domain\User\Role;
use Neutrino\Domain\User\User;

class LoadCustomerData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $user = new User(
            new Email('demo@gmail.com'),
            new Password('1'),
            'Morgan',
            'Freeman'
        );
        $manager->persist($user);

        $account = new Account('default');
        $manager->persist($account);

        $store = new Store(
            account: $account,
            name: 'Demo Store',
            slug: StoreSlug::fromGenerator(new RandomSlugGenerator())
        );
        $manager->persist($store);

        $account->addStore($store);

        $role = AccountRole::Owner;

        $accountMembership = new AccountMembership($account, $user, $role);
        $manager->persist($accountMembership);

        $user->addRole(new Role('customer', 'backoffice'));
        $user->addAccountMembership($accountMembership);

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 4;
    }
}
