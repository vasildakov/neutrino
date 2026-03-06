<?php

declare(strict_types=1);

namespace Neutrino\Domain\User;

interface UserRepositoryInterface extends \Mezzio\Authentication\UserRepositoryInterface
{
    public function findOneByEmail(string $email): ?User;

    public function create(User $user): void;
}
