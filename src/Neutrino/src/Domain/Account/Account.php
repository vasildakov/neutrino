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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Neutrino\Domain\Billing\Subscription;
use Neutrino\Domain\Payment\Payment;
use Neutrino\Domain\Store\Store;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'accounts')]
class Account
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected UuidInterface|string $id;

    /** @var Collection<int, AccountMembership> */
    #[ORM\OneToMany(
        targetEntity: AccountMembership::class,
        mappedBy: 'account',
        cascade: ['persist'],
        orphanRemoval: true
    )]
    private Collection $memberships;

    /** @var Collection<int, Store> */
    #[ORM\OneToMany(
        targetEntity: Store::class,
        mappedBy: 'account',
        cascade: ['persist'],
        orphanRemoval: true
    )]
    private Collection $stores;

    /** @var Collection<int, Payment> */
    #[ORM\OneToMany(targetEntity: Payment::class, mappedBy: 'account')]
    private Collection $payments;

    #[ORM\OneToOne(targetEntity: Subscription::class, mappedBy: 'account')]
    private ?Subscription $subscription = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(
        #[ORM\Column(type: 'string', length: 120)]
        private string $name,
    ) {
        $this->memberships = new ArrayCollection();
        $this->stores      = new ArrayCollection();
        $this->createdAt   = new DateTimeImmutable();
    }

    public function id(): UuidInterface|string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    /** @return list<Store> */
    public function stores(): array
    {
        return $this->stores->toArray();
    }

    public function addMembership(AccountMembership $membership): void
    {
        $this->memberships->add($membership);
    }

    public function cancelMembership(AccountMembership $membership): void
    {
        $this->memberships->removeElement($membership);
    }

    public function addStore(Store $store): void
    {
        $this->stores->add($store);
    }

    public function removeStore(Store $store): void
    {
        $this->stores->removeElement($store);
    }

    /**
     * @return Collection<int, Store>
     */
    public function getStores(): Collection
    {
        return $this->stores;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function subscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function setSubscription(?Subscription $subscription): void
    {
        $this->subscription = $subscription;
    }

    /**
     * @return Collection<int, Payment>
     */
    public function payments(): Collection
    {
        return $this->payments;
    }
}
