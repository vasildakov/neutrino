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

namespace Neutrino\Authentication\Provider;

use League\OAuth2\Client\Provider\GenericProvider;

/**
 * LinkedIn OAuth provider.
 *
 * We intentionally extend {@see GenericProvider} instead of using
 * {@see \League\OAuth2\Client\Provider\LinkedIn}.
 *
 * The provider from the `league/oauth2-linkedin` package is based on the
 * legacy LinkedIn API and expects the following scopes:
 *
 *   - r_liteprofile
 *   - r_emailaddress
 *
 * It also calls the deprecated endpoints:
 *
 *   - https://api.linkedin.com/v2/me
 *   - https://api.linkedin.com/v2/clientAwareMemberHandles
 *
 * Our LinkedIn application is configured to use the modern OpenID Connect
 * flow ("Sign In with LinkedIn") with scopes:
 *
 *   - openid
 *   - profile
 *   - email
 *
 * In this flow the user information must be retrieved from:
 *
 *   https://api.linkedin.com/v2/userinfo
 *
 * Because of this mismatch the official LinkedIn provider throws:
 *
 *   LinkedInAccessDeniedException:
 *   "Not enough permissions to access: me.GET.NO_VERSION"
 *
 * Using {@see GenericProvider} allows us to configure the correct OAuth
 * endpoints and consume the OIDC userinfo response instead.
 *
 * This class exists mainly to provide a dedicated, type-safe provider for
 * LinkedIn within the authentication module.
 */
final class LinkedinProvider extends GenericProvider
{
}
