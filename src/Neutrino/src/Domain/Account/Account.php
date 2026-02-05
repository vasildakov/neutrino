<?php

declare(strict_types=1);

namespace Neutrino\Domain\Account;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    #[ORM\OneToMany(targetEntity: AccountMembership::class, mappedBy: 'account', cascade: ['persist'], orphanRemoval: true)]
    private Collection $memberships;

    /** @var Collection<int, Store> */
    #[ORM\OneToMany(targetEntity: Store::class, mappedBy: 'account', cascade: ['persist'], orphanRemoval: true)]
    private Collection $stores;

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


    public function id(): AccountId
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

    public function addStore(Store $store): void
    {
        if ($store->accountId()->value !== $this->id->value) {
            throw new \DomainException('Store must belong to this account.');
        }

        if (!$this->stores->contains($store)) {
            $this->stores->add($store);
        }
    }

    public function addMembership(AccountMembership $membership): void
    {
        if ($membership->accountId()->value !== $this->id->value) {
            throw new \DomainException('Membership must belong to this account.');
        }

        if (!$this->memberships->contains($membership)) {
            $this->memberships->add($membership);
        }
    }
}
