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

namespace Neutrino\Service\Payment;

use InvalidArgumentException;

use function array_filter;
use function array_values;
use function count;
use function explode;
use function preg_match;
use function preg_replace;
use function trim;

final class OmnipayCardMapper
{
    /**
     * @param array{
     *   cardNumber?: string|null,
     *   cardName?: string|null,
     *   expiryDate?: string|null,
     *   cvv?: string|null,
     *   firstName?: string|null,
     *   lastName?: string|null,
     *   address?: string|null,
     *   address2?: string|null,
     *   city?: string|null,
     *   zip?: string|null,
     *   country?: string|null
     * } $data
     * @return array{
     *   number: string,
     *   cvv: string,
     *   expiryMonth: int,
     *   expiryYear: int,
     *   name?: string,
     *   firstName?: string,
     *   lastName?: string,
     *   billingAddress1?: string,
     *   billingAddress2?: string,
     *   billingCity?: string,
     *   billingPostcode?: string,
     *   billingCountry?: string
     * }
     */
    public static function fromCheckout(array $data): array
    {
        $numberRaw = (string) ($data['cardNumber'] ?? '');
        $name      = trim((string) ($data['cardName'] ?? ''));
        $expiry    = trim((string) ($data['expiryDate'] ?? ''));
        $cvv       = trim((string) ($data['cvv'] ?? ''));

        $number = (string) preg_replace('/\D+/', '', $numberRaw); // remove spaces/dashes

        if ($number === '' || $cvv === '' || $expiry === '') {
            throw new InvalidArgumentException('Missing card fields.');
        }

        // expiryDate: "12/28" => month=12, year=2028
        [$m, $y] = self::parseExpiryMmYy($expiry);

        $card = [
            'number'      => $number,
            'cvv'         => $cvv,
            'expiryMonth' => $m,
            'expiryYear'  => $y,
        ];

        if ($name !== '') {
            $card['name'] = $name;

            // If you want, also split to first/last for gateways that use them:
            $parts = array_values(array_filter(explode(' ', $name), fn ($p) => $p !== ''));
            if (count($parts) >= 2) {
                $card['firstName'] = $parts[0];
                $card['lastName']  = $parts[count($parts) - 1];
            }
        }

        // Optional billing info (helps for real gateways / fraud checks)
        if (! empty($data['address'])) {
            $card['billingAddress1'] = (string) $data['address'];
        }
        if (! empty($data['address2'])) {
            $card['billingAddress2'] = (string) $data['address2'];
        }
        if (! empty($data['city'])) {
            $card['billingCity'] = (string) $data['city'];
        }
        if (! empty($data['zip'])) {
            $card['billingPostcode'] = (string) $data['zip'];
        }
        if (! empty($data['country'])) {
            $card['billingCountry'] = (string) $data['country'];
        }

        return $card;
    }

    /**
     * @return array{0:int,1:int} [month, year]
     */
    private static function parseExpiryMmYy(string $expiry): array
    {
        // allow "MM/YY" or "MM/YYYY"
        if (! preg_match('#^(0[1-9]|1[0-2])\s*/\s*(\d{2}|\d{4})$#', $expiry, $m)) {
            throw new InvalidArgumentException('Invalid expiryDate format (expected MM/YY).');
        }

        $month = (int) $m[1];
        $year  = (int) $m[2];

        if ($year < 100) {
            $year += 2000; // 28 => 2028
        }

        return [$month, $year];
    }
}
