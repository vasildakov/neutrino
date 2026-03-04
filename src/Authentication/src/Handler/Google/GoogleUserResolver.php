<?php

namespace Neutrino\Authentication\Handler\Google;

final class GoogleUserResolver implements GoogleUserResolverInterface
{
    public function resolve(
        string $googleId,
        string $email,
        string $name,
        string $accessToken,
        ?string $refreshToken,
        ?int $expires,
    ): GoogleUserResolveResult {

        // For now: accept the user and use Google ID as the user id
        return GoogleUserResolveResult::success($googleId);
    }
}
