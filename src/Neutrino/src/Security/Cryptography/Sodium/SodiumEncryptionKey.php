<?php

declare(strict_types=1);

namespace Neutrino\Security\Cryptography\Sodium;

use Neutrino\Security\Cryptography\CryptographyKeyInterface;
use Random\RandomException;
use RuntimeException;

final readonly class SodiumEncryptionKey implements CryptographyKeyInterface
{
    private string $binary;

    /**
     * Create from raw binary (advanced usage only).
     */
    private function __construct(string $binary)
    {
        if (strlen($binary) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            throw new RuntimeException('SecretKey must be exactly 32 bytes.');
        }

        $this->binary = $binary;
    }

    /**
     * Create from raw binary (advanced usage only).
     */
    public static function fromBinary(string $binary): self
    {
        return new self($binary);
    }

    /**
     * Create from base64 (env / config / secrets).
     */
    public static function fromBase64(string $base64): self
    {
        $binary = base64_decode($base64, true);

        if ($binary === false) {
            throw new RuntimeException('SecretKey base64 decoding failed.');
        }

        return new self($binary);
    }

    /**
     * Generate a new random key (one-time setup / tests).
     * @throws RandomException
     */
    public static function generate(): self
    {
        return new self(
            random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES)
        );
    }

    /**
     * Internal use only (crypto).
     */
    public function binary(): string
    {
        return $this->binary;
    }

    /**
     * Safe transport/storage representation.
     */
    public function toBase64(): string
    {
        return base64_encode($this->binary);
    }

    public function algorithm(): string
    {
        return 'sodium-secretbox';
    }
}
