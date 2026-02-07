<?php

declare(strict_types=1);

namespace Neutrino\View\Helper;

use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\DefaultUser;

final class Avatar
{
    public function __construct()
    {
    }

    public function __invoke(UserInterface|array $user): string
    {
        if ($user instanceof DefaultUser) {
            return $user->getDetail('avatar');
        }

        if (is_array($user) && isset($user['details']['avatar'])) {
            return $user['details']['avatar'];
        }

        return '/uploads/avatar.png';
    }
}
