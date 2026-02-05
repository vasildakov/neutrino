<?php

declare(strict_types=1);

namespace Neutrino\Security\Cryptography\Sodium;

use Neutrino\Security\Cryptography\CryptographyInterface;
use Random\RandomException;
use RuntimeException;
use SodiumException;
use function base64_decode;
use function base64_encode;
use function random_bytes;
use function sodium_crypto_secretbox;
use function sodium_crypto_secretbox_open;
use function strlen;
use function substr;
use const SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;

/**
 * @see https://www.php.net/manual/en/function.sodium-crypto-secretbox.php
 */
final readonly class SodiumCryptography implements CryptographyInterface
{
    public function __construct(private SodiumEncryptionKey $key) // 32 bytes binary
    {
        if ($key->algorithm() !== 'sodium-secretbox') {
            throw new RuntimeException('Invalid key for SodiumCryptography.');
        }
    }

    /**
     * Encrypts a payload using Sodium secret box algorithm.
     *
     * @param string $plaintext Plain text to encrypt
     * @throws RandomException
     * @throws SodiumException
     */
    public function encrypt(string $plaintext): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = sodium_crypto_secretbox(
            message: $plaintext,
            nonce: $nonce,
            key: $this->key->binary()
        );

        return base64_encode($nonce . $cipher);
    }

    /**
     * Decrypts a payload using Sodium secret box algorithm.
     *
     * @param string $ciphertext Base64 encoded payload
     * @throws SodiumException
     */
    public function decrypt(string $ciphertext): string
    {
        $bin = base64_decode($ciphertext, true);
        if ($bin === false) {
            throw new RuntimeException('Invalid payload.');
        }

        $nonceLen = SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;
        if (strlen($bin) < $nonceLen) {
            throw new RuntimeException('Invalid payload length.');
        }

        $nonce  = substr($bin, 0, $nonceLen);
        $cipher = substr($bin, $nonceLen);

        $plain = sodium_crypto_secretbox_open(
            $cipher,
            $nonce,
            $this->key->binary()
        );

        if ($plain === false) {
            throw new \RuntimeException('Decryption failed.');
        }

        return $plain;
    }
}
