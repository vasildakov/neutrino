<?php

declare(strict_types=1);

namespace Neutrino\Service;

use Doctrine\ORM\EntityManagerInterface;
use Neutrino\Consent\CookieSigner;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class ConsentServiceFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ConsentService
    {
        $em = $container->get(EntityManagerInterface::class);

        // Get HMAC key from environment
        $hmacKey = $_ENV['CONSENT_HMAC_KEY'] ?? 'change-me-32+bytes-random';
        $signer  = new CookieSigner($hmacKey);

        $cookieName = $_ENV['CONSENT_COOKIE_NAME'] ?? 'neutrino_consent';

        return new ConsentService($em, $signer, $cookieName);
    }
}
