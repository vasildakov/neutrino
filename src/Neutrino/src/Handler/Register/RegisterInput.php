<?php

declare(strict_types=1);

namespace Neutrino\Handler\Register;

final class RegisterInput
{
    public function __construct(
        public readonly string $email,
        public readonly string $password
    ) {
    }
}
