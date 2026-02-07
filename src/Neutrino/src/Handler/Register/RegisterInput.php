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
namespace Neutrino\Handler\Register;

final readonly class RegisterInput
{
    public function __construct(
        public string $email,
        public string $password,
        public ?string $role = null
    ) {
    }
}
