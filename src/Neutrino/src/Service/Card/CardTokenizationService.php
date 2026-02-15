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

namespace Neutrino\Service\Card;

use RuntimeException;
use function base64_decode;
use function base64_encode;
use function bin2hex;
use function openssl_cipher_iv_length;
use function openssl_decrypt;
use function openssl_encrypt;
use function openssl_random_pseudo_bytes;
use function preg_match;
use function preg_replace;
use function str_repeat;
use function strlen;
use function substr;
use const OPENSSL_RAW_DATA;

/**
 * Card Tokenization Service
 *
 * IMPORTANT SECURITY NOTES:
 * 1. This should ONLY be used if you are PCI DSS Level 1 certified
 * 2. Consider using payment gateway tokenization (Stripe, PayPal) instead
 * 3. Requires proper key management (HSM recommended for production)
 * 4. NEVER store CVV - even encrypted
 * 5. Must have annual security audits
 *
 * For most applications, use payment gateway tokens instead of this!
 */
class CardTokenizationService
{
    private const CIPHER = 'aes-256-gcm';

    public function __construct(
        private readonly string $encryptionKey
    ) {
        if (empty($this->encryptionKey)) {
            throw new RuntimeException('Encryption key is required for card tokenization');
        }

        if (strlen($this->encryptionKey) !== 32) {
            throw new RuntimeException('Encryption key must be exactly 32 bytes (256 bits)');
        }
    }

    /**
     * Tokenize card data (encrypt PAN, return token reference)
     *
     * @param array<string, mixed> $cardData
     * @return array{token: string, last4: string, brand: string, expiry: string}
     */
    public function tokenize(array $cardData): array
    {
        $pan            = $cardData['number'] ?? '';
        $expiryMonth    = $cardData['expiry_month'] ?? '';
        $expiryYear     = $cardData['expiry_year'] ?? '';
        $cardholderName = $cardData['name'] ?? '';

        // Validate required fields
        if (empty($pan) || empty($expiryMonth) || empty($expiryYear)) {
            throw new RuntimeException('Card number and expiry date are required');
        }

        // Remove spaces and dashes from PAN
        $pan = (string) preg_replace('/[\s-]/', '', $pan);

        // Extract last 4 digits (safe to store unencrypted per PCI DSS)
        $last4 = substr($pan, -4);

        // Detect card brand from BIN (first 6 digits)
        $brand = $this->detectCardBrand($pan);

        // Encrypt the full PAN
        $encryptedPan = $this->encrypt($pan);

        // Generate token reference (unique identifier for this card)
        $token = 'card_' . bin2hex(openssl_random_pseudo_bytes(16));

        return [
            'token'           => $token,
            'encrypted_pan'   => $encryptedPan,
            'last4'           => $last4,
            'brand'           => $brand,
            'expiry_month'    => $expiryMonth,
            'expiry_year'     => $expiryYear,
            'cardholder_name' => $cardholderName,
        ];
    }

    /**
     * Detokenize (decrypt) card data
     *
     * @throws RuntimeException
     */
    public function detokenize(string $encryptedPan): string
    {
        return $this->decrypt($encryptedPan);
    }

    /**
     * Encrypt sensitive data using AES-256-GCM
     */
    private function encrypt(string $plaintext): string
    {
        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        if (! is_int($ivLength)) {
            throw new RuntimeException('Failed to get IV length');
        }

        $iv  = openssl_random_pseudo_bytes($ivLength);
        $tag = '';

        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            16
        );

        if ($ciphertext === false) {
            throw new RuntimeException('Encryption failed');
        }

        // Combine IV + ciphertext + tag and encode
        return base64_encode($iv . $ciphertext . $tag);
    }

    /**
     * Decrypt data encrypted with AES-256-GCM
     */
    private function decrypt(string $encrypted): string
    {
        $data = base64_decode($encrypted, true);
        if ($data === false) {
            throw new RuntimeException('Invalid encrypted data');
        }

        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        if (! is_int($ivLength)) {
            throw new RuntimeException('Failed to get IV length');
        }

        $iv         = substr($data, 0, $ivLength);
        $tag        = substr($data, -16);
        $ciphertext = substr($data, $ivLength, -16);

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($plaintext === false) {
            throw new RuntimeException('Decryption failed');
        }

        return $plaintext;
    }

    /**
     * Detect card brand from BIN (Bank Identification Number)
     */
    private function detectCardBrand(string $pan): string
    {
        // Remove any non-digits
        $pan = (string) preg_replace('/\D/', '', $pan);

        // Visa: starts with 4
        if (str_starts_with($pan, '4')) {
            return 'Visa';
        }

        // Mastercard: 51-55 or 2221-2720
        if (preg_match('/^5[1-5]/', $pan) || preg_match('/^2[2-7]/', $pan)) {
            return 'Mastercard';
        }

        // American Express: starts with 34 or 37
        if (preg_match('/^3[47]/', $pan)) {
            return 'American Express';
        }

        // Discover: starts with 6011, 622126-622925, 644-649, 65
        if (preg_match('/^6011|^622[1-9]|^64[4-9]|^65/', $pan)) {
            return 'Discover';
        }

        return 'Unknown';
    }

    /**
     * Mask card number for display (show only last 4)
     */
    public static function maskCardNumber(string $pan): string
    {
        $pan    = (string) preg_replace('/\D/', '', $pan);
        $length = strlen($pan);

        if ($length < 4) {
            return str_repeat('*', $length);
        }

        return str_repeat('*', $length - 4) . substr($pan, -4);
    }
}
