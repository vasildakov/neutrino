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
namespace Neutrino\Domain\Store;

use Random\RandomException;

use function random_bytes;
use function bin2hex;

final class RandomSlugGenerator implements SlugGeneratorInterface
{
    /**
     * @throws RandomException
     */
    public function generate(): string
    {
        return bin2hex(random_bytes(8));
    }
}
