<?php

declare(strict_types=1);

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
