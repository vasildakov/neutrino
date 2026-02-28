<?php

declare(strict_types=1);

namespace Neutrino\Domain\Analytics;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class Country
{
    #[ORM\Column(name: 'name', type: Types::STRING, length: 255, nullable: true)]
    private ?string $name = null;

    // ISO 3166-1 alpha-2 is 2, alpha-3 is 3. You currently use length 3.
    #[ORM\Column(name: 'iso_code', type: Types::STRING, length: 3, nullable: true)]
    private ?string $isoCode = null;

    public function __construct(?string $name = null, ?string $isoCode = null)
    {
        $this->name    = $name;
        $this->isoCode = $isoCode;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function isoCode(): ?string
    {
        return $this->isoCode;
    }

    public function isEmpty(): bool
    {
        return ($this->name === null || $this->name === '')
            && ($this->isoCode === null || $this->isoCode === '');
    }
}
