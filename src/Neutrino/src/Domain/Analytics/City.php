<?php

declare(strict_types=1);

namespace Neutrino\Domain\Analytics;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class City
{
    #[ORM\Column(name: 'name', type: Types::STRING, length: 255, nullable: true)]
    private ?string $name = null;

    public function __construct(?string $name = null)
    {
        $this->name = $name;
    }

    public function name(): ?string
    {
        return $this->name;
    }
}
