<?php

declare(strict_types=1);

namespace Neutrino\Authentication\Resolver;

use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Provider\LinkedInResourceOwner;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Smolblog\OAuth2\Client\Provider\TwitterUser;

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

    public static function fromGenericResourceOwner(GenericResourceOwner $owner): self
    {
        $data = $owner->toArray();

        $providerUserId = isset($data['sub']) && is_string($data['sub'])
            ? $data['sub']
            : (string) $owner->getId();

        $email = isset($data['email']) && is_string($data['email'])
            ? $data['email']
            : null;

        $name = isset($data['given_name']) && is_string($data['given_name'])
            ? $data['given_name']
            : null;

        $surname = isset($data['family_name']) && is_string($data['family_name'])
            ? $data['family_name']
            : null;

        return new self(
            provider: 'linkedin',
            providerUserId: $providerUserId,
            email: $email,
            name: $name,
            surname: $surname
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
