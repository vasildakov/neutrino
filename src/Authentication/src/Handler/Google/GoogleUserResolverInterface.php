<?php

namespace Neutrino\Authentication\Handler\Google;

interface GoogleUserResolverInterface
{
    public function resolve(
        string $googleId,
        string $email,
        string $name,
        string $accessToken,
        ?string $refreshToken,
        ?int $expires,
    ): GoogleUserResolveResult;
}
