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

namespace Neutrino\Authentication\Resolver;

use Mezzio\Session\SessionInterface;
use Neutrino\Domain\User\User;

final class RedirectResolver
{
    public function resolve(User $user, SessionInterface $session): string
    {
        // Check for the intended URL (e.g., a user tried to access the protected page before login)
        $intended = $session->get('intended_url');

        if (is_string($intended) && str_starts_with($intended, '/')) {
            $session->unset('intended_url');
            return $intended;
        }

        // Default redirect based on user scope
        $scope = $user->getScope();

        return match ($scope) {
            'platform'  => '/platform',
            'dashboard' => '/dashboard',
            default     => '/',
        };
    }
}
