<?php

declare(strict_types=1);

namespace Neutrino\Domain\Account;

use Ramsey\Uuid\Uuid;

class AccountId
{
    private function __construct(public string $value) {}

    public static function new(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }

}
