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

use Psr\Container\ContainerInterface;
use RuntimeException;
use function base64_decode;
use function strlen;

class CardTokenizationServiceFactory
{
    public function __invoke(ContainerInterface $container): CardTokenizationService
    {
        // Get encryption key from the environment
        $encryptionKey = $_ENV['CARD_ENCRYPTION_KEY'] ?? '';

        if (empty($encryptionKey)) {
            throw new RuntimeException(
                'CARD_ENCRYPTION_KEY environment variable is required. '
                . 'Generate with: php bin/generate-encryption-key.php'
            );
        }

        // Decode the base64-encoded key
        $key = base64_decode($encryptionKey, true);

        if ($key === false || strlen($key) !== 32) {
            throw new RuntimeException('CARD_ENCRYPTION_KEY must be a valid base64-encoded 32-byte key');
        }

        return new CardTokenizationService($key);
    }
}
