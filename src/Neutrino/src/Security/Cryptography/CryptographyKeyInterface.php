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
namespace Neutrino\Security\Cryptography;

/**
 * Marker + access interface for cryptographic keys.
 */
interface CryptographyKeyInterface
{
    /**
     * Returns raw binary key material.
     *
     * For cryptographic use only.
     */
    public function binary(): string;

    /**
     * Human-readable identifier (for rotation / debugging).
     */
    public function algorithm(): string;
}
