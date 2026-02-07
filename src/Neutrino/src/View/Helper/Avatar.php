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
namespace Neutrino\View\Helper;

use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\DefaultUser;

use function is_array;

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
