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
namespace Neutrino\Security\Cryptography;

interface CryptographyInterface
{
    /**
     * Encrypt a tenant database secret for storage.
     */
    public function encrypt(string $plaintext): string;

    /**
     * Decrypt a tenant database secret for usage.
     */
    public function decrypt(string $ciphertext): string;
}
