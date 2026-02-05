<?php

declare(strict_types=1);

namespace Neutrino\Security\Cryptography\OpenSsl;

use Neutrino\Security\Cryptography\CryptographyKeyInterface;
use Random\RandomException;
use RuntimeException;
use function base64_decode;
use function base64_encode;
use function random_bytes;
use function strlen;

final readonly class OpenSslAes256Key implements CryptographyKeyInterface
{
    private string $binary;

    private function __construct(string $binary)
    {
        if (strlen($binary) !== 32) {
            throw new RuntimeException('OpenSslAes256Key must be exactly 32 bytes.');
        }

        $this->binary = $binary;
    }

    public static function fromBase64(string $base64): self
    {
        $bin = base64_decode($base64, true);
        if ($bin === false) {
            throw new RuntimeException('Invalid base64 for OpenSslAes256Key.');
        }

        return new self($bin);
    }

    /**
     * @throws RandomException
     */
    public static function generate(): self
    {
        return new self(random_bytes(32));
    }

    public function binary(): string
    {
        return $this->binary;
    }

    public function toBase64(): string
    {
        return base64_encode($this->binary);
    }

    public function algorithm(): string
    {
        return 'openssl-aes-256-gcm';
    }
}
