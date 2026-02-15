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

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'user_roles')]
#[ORM\UniqueConstraint(name: 'uniq_user_role_role', columns: ['user_id', 'role_id'])]
#[ORM\Index(name: 'idx_user_roles_user', columns: ['user_id'])]
class UserRole
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'roles')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Role::class, cascade: ['persist'], inversedBy: 'users')]
    #[ORM\JoinColumn(name: 'role_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    private Role $role;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(User $user, Role $role)
    {
        $this->id = Uuid::uuid4();
        $this->user = $user;
        $this->role = $role;
        $this->createdAt = new DateTimeImmutable();
    }

    public function id(): UuidInterface
    {
        return $this->id;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getRole(): Role
    {
        return $this->role;
    }
}