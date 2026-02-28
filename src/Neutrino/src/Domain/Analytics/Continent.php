<?php

declare(strict_types=1);

namespace Neutrino\Domain\Analytics;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class Continent
{
    #[ORM\Column(name: 'name', type: Types::STRING, length: 15, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(name: 'code', type: Types::STRING, length: 2, nullable: true)]
    private ?string $code = null;

    public function __construct(?string $name = null, ?string $code = null)
    {
        $this->name = $name;
        $this->code = $code;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function code(): ?string
    {
        return $this->code;
    }

    public function isEmpty(): bool
    {
        return ($this->name === null || $this->name === '')
            && ($this->code === null || $this->code === '');
    }
}
