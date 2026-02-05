<?php

namespace Neutrino\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Neutrino\Domain\User\User;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class UserRepositoryFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): UserRepository
    {
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        assert($em instanceof EntityManagerInterface);

        /** @var ClassMetadata $metadata */
        $metadata = $em->getClassMetadata(User::class);
        assert($metadata instanceof ClassMetadata);


        $repository = new UserRepository($em, $metadata);

        return $repository;
    }
}
