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
namespace Neutrino\Domain\User;

use Neutrino\Repository\UserActivityRepository;
use Psr\Http\Message\ServerRequestInterface;

final readonly class UserActivityLogger
{
    public function __construct(
        private UserActivityRepository $repository
    ) {
    }

    public function log(
        User $user,
        string $activityType,
        string $description,
        ?ServerRequestInterface $request = null
    ): void {
        $ipAddress = null;
        $userAgent = null;
        $city = null;

        if ($request !== null) {
            $serverParams = $request->getServerParams();
            $ipAddress = $serverParams['REMOTE_ADDR'] ?? null;
            $userAgent = $serverParams['HTTP_USER_AGENT'] ?? null;

            // City could be determined from IP geolocation service
            // For now, we'll leave it null or set from external service
        }

        $activity = new UserActivity(
            user: $user,
            activityType: $activityType,
            description: $description,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            city: $city
        );

        $this->repository->save($activity);
    }

    public function logLogin(User $user, ?ServerRequestInterface $request = null): void
    {
        $this->log($user, 'login', 'User logged in successfully', $request);
    }

    public function logLogout(User $user, ?ServerRequestInterface $request = null): void
    {
        $this->log($user, 'logout', 'User logged out', $request);
    }

    public function logProfileUpdate(User $user, ?ServerRequestInterface $request = null): void
    {
        $this->log($user, 'update', 'Profile information updated', $request);
    }

    public function logPasswordChange(User $user, ?ServerRequestInterface $request = null): void
    {
        $this->log($user, 'password', 'Password changed', $request);
    }

    public function logView(User $user, string $resource, ?ServerRequestInterface $request = null): void
    {
        $this->log($user, 'view', "Viewed {$resource}", $request);
    }
}

