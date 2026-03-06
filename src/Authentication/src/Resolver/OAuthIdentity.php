<?php

declare(strict_types=1);

namespace Neutrino\Authentication\Resolver;

use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

final readonly class OAuthIdentity
{
    public function __construct(
        public string $provider,
        public string $providerUserId,
        public ?string $email,
        public ?string $name,
        public ?string $surname
    ) {}

    public static function fromGoogleUser(GoogleUser $user): self
    {
        return new self(
            provider: 'google',
            providerUserId: (string) $user->getId(),
            email: $user->getEmail(),
            name: $user->getFirstName(),
            surname: $user->getLastName(),
        );
    }

    public static function fromResourceOwner(
        string $provider,
        ResourceOwnerInterface $owner
    ): self {
        $data = $owner->toArray();

        return new self(
            provider: $provider,
            providerUserId: (string) $owner->getId(),
            email: $data['email'] ?? null,
            name: $data['given_name'] ?? null,
            surname: $data['family_name'] ?? null,
        );
    }
}
