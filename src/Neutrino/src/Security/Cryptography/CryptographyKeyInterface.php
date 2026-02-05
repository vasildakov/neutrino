<?php

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
