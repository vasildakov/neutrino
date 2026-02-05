<?php

namespace Neutrino\Security\Authorization;

use Laminas\Permissions\Acl\Acl;

interface AclProviderInterface
{
    public function getAcl(): Acl;
}
