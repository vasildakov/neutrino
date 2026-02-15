<?php

declare(strict_types=1);
/*
 * This file is part of Neutrino.
 *
 * (c) Vasil Dakov <vasildakov@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Neutrino\Domain\Account;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Neutrino\Domain\User\User;

#[ORM\Entity]
#[ORM\Table(name: 'account_memberships')]
#[ORM\UniqueConstraint(name: 'uniq_account_user', columns: ['account_id', 'user_id'])]
class AccountMembership
{
    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Account::class, inversedBy: 'memberships')]
        #[ORM\JoinColumn(name: 'account_id', referencedColumnName: 'id', nullable: false)]
        private Account $account,

        #[ORM\Id]
        #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'accountMemberships')]
        #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        private User $user,

        #[ORM\Column(type: 'string', length: 16, enumType: AccountRole::class)]
        private AccountRole $role,
    ) {
        $this->createdAt = new DateTimeImmutable();
    }

    public static function owner(Account $account, User $user): self
    {
        return new self($account, $user, AccountRole::Owner);
    }

    public function account(): Account
    {
        return $this->account;
    }

    public function user(): User
    {
        return $this->user;
    }

    public function role(): AccountRole
    {
        return $this->role;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
