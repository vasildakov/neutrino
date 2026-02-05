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

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'role_permissions')]
#[ORM\UniqueConstraint(
    name: 'uniq_role_resource_privilege',
    columns: ['role_id', 'resource_id', 'privilege_id']
)]
class RolePermission
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Role::class, inversedBy: 'permissions')]
    #[ORM\JoinColumn(name: 'role_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Role $role;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Resource::class)]
    #[ORM\JoinColumn(name: 'resource_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Resource $resource;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Privilege::class)]
    #[ORM\JoinColumn(name: 'privilege_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Privilege $privilege;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $allowed = true;

    public function __construct(Role $role, Resource $resource, Privilege $privilege, bool $allowed = true)
    {
        $this->role      = $role;
        $this->resource  = $resource;
        $this->privilege = $privilege;
        $this->allowed   = $allowed;
    }

    public function role(): Role { return $this->role; }
    public function resource(): Resource { return $this->resource; }
    public function privilege(): Privilege { return $this->privilege; }
    public function allowed(): bool { return $this->allowed; }
}