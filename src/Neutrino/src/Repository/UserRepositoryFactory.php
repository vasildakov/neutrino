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
namespace Neutrino\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Neutrino\Domain\User\User;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final readonly class UserRepositoryFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): UserRepository
    {
        $em = $container->get(EntityManagerInterface::class);
        assert($em instanceof EntityManagerInterface);

        $metadata = $em->getClassMetadata(User::class);
        assert($metadata instanceof ClassMetadata);

        return new UserRepository($em, $metadata);
    }
}
