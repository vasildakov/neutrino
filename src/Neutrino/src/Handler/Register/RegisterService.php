<?php

declare(strict_types=1);

namespace Neutrino\Handler\Register;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Neutrino\Domain\User\Email;
use Neutrino\Domain\User\Password;
use Neutrino\Domain\User\User;
use Neutrino\Repository\UserRepository;

final class RegisterService
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
    }

    public function register(RegisterInput $input): User
    {
        $email = new Email($input->email);
        $password = new Password($input->password);

        /** @var UserRepository $repository */
        $repository = $this->em->getRepository(User::class);

        if ($repository->findOneByEmail($email->getValue())) {
            throw new InvalidArgumentException('Email already registered');
        }

        $user = new User(email: $email, password: $password);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
