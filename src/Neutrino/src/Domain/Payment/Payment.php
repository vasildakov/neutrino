<?php

namespace Neutrino\Domain\Payment;

use Doctrine\ORM\Mapping as ORM;
use Neutrino\Domain\Order\Order;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'payments')]
#[ORM\Index(name: 'idx_payments_order', columns: ['order_id'])]
#[ORM\Index(name: 'idx_payments_provider_ref', columns: ['provider', 'provider_ref'])]
class Payment
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected UuidInterface|string $id;

    #[ORM\ManyToOne(targetEntity: Order::class)]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', nullable: false)]
    private Order $order;

    #[ORM\Column(type: "string", length: 50)]
    private string $provider;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $provider_ref = null;

}
