<?php

declare(strict_types=1);

namespace Neutrino\Domain\User;

enum RoleScope: string
{
    case PLATFORM = 'platform';
    case STORE    = 'store';
}
