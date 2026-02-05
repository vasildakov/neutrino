<?php

namespace Neutrino\Security\Cryptography\OpenSsl;

use Neutrino\Security\Cryptography\CryptographyInterface;
use Random\RandomException;
use RuntimeException;

final readonly class OpenSslCryptography implements CryptographyInterface
{
    private const CIPHER = 'aes-256-gcm';
    private const IV_LEN = 12; // 96-bit IV is standard for GCM
    private const TAG_LEN = 16;

    public function __construct(
        private OpenSslAes256Key $key,
    ) {}

    /**
     * @throws RandomException
     */
    public function encrypt(string $plaintext): string
    {
        $iv = random_bytes(self::IV_LEN);
        $tag = '';

        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $this->key->binary(),
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',           // AAD (optional)
            self::TAG_LEN // tag length
        );

        if ($ciphertext === false || $tag === '') {
            throw new RuntimeException('OpenSSL encryption failed.');
        }

        return base64_encode($iv . $tag . $ciphertext);
    }

    public function decrypt(string $ciphertext): string
    {
        $bin = base64_decode($ciphertext, true);
        if ($bin === false) {
            throw new RuntimeException('Invalid encrypted payload.');
        }

        if (strlen($bin) < (self::IV_LEN + self::TAG_LEN + 1)) {
            throw new RuntimeException('Invalid encrypted payload length.');
        }

        $iv  = substr($bin, 0, self::IV_LEN);
        $tag = substr($bin, self::IV_LEN, self::TAG_LEN);
        $ct  = substr($bin, self::IV_LEN + self::TAG_LEN);

        $plain = openssl_decrypt(
            $ct,
            self::CIPHER,
            $this->key->binary(),
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '' // AAD (must match encrypt if you use it)
        );

        if ($plain === false) {
            throw new RuntimeException('OpenSSL decryption failed.');
        }

        return $plain;
    }
}