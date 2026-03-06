<?php

declare(strict_types=1);

namespace Neutrino\Consent;

use RuntimeException;

use function base64_decode;
use function base64_encode;
use function count;
use function explode;
use function hash_equals;
use function hash_hmac;
use function is_array;
use function json_decode;
use function json_encode;
use function rtrim;
use function str_repeat;
use function strlen;
use function strtr;

use const JSON_UNESCAPED_SLASHES;

final class CookieSigner
{
    public function __construct(private string $hmacKey)
    {
    }

    public function sign(array $payload): string
    {
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new RuntimeException('Failed to encode consent payload.');
        }

        $p   = $this->b64url($json);
        $sig = $this->b64url(hash_hmac('sha256', $p, $this->hmacKey, true));

        return $p . '.' . $sig;
    }

    /**
     * @param string $token
     * @return array|null
     */
    public function verify(string $token): ?array
    {
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) {
            return null;
        }

        [$p, $sig] = $parts;
        $expected  = $this->b64url(hash_hmac('sha256', $p, $this->hmacKey, true));

        if (! hash_equals($expected, $sig)) {
            return null;
        }

        $json = $this->b64urlDecode($p);
        if ($json === null) {
            return null;
        }

        $data = json_decode($json, true);
        return is_array($data) ? $data : null;
    }

    private function b64url(string $s): string
    {
        return rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
    }

    private function b64urlDecode(string $s): ?string
    {
        $s   = strtr($s, '-_', '+/');
        $pad = strlen($s) % 4;
        if ($pad !== 0) {
            $s .= str_repeat('=', 4 - $pad);
        }
        $out = base64_decode($s, true);
        return $out === false ? null : $out;
    }
}
