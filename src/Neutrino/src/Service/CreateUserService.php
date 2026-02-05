<?php

declare(strict_types=1);

namespace Neutrino\Service;

use Doctrine\ORM\EntityManagerInterface;
use Neutrino\Domain\User\User;

final class CreateUserService
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {}

    /**
     * @todo replace the array with UserInput
     * @param array $data
     * @return Closure|mixed
     */
    public function __invoke(array $data): mixed
    {
        return $this->em->wrapInTransaction(function () use ($data): User {
            $user = new User(
                $data['email'],
                $data['password'],
            );
            $this->em->persist($user);

            // outbox message stored in the SAME DB transaction
//            $message = new OutboxMessage(
//                type: 'user.created',
//                payload: [
//                    'orderId' => $user->getId(),
//                    'email'  => $user->getEmail(),
//                ]
//            );
//            $this->em->persist($message);

            $this->em->flush();
            return $user;
        });
    }
}
