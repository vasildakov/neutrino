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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Laminas\Permissions\Acl\Role\RoleInterface;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'roles')]
#[ORM\UniqueConstraint(name: 'uniq_role_name_scope', columns: ['name', 'scope'])]
class Role implements RoleInterface
{
    public const SCOPE_PLATFORM = 'platform';
    public const SCOPE_STORE = 'store';


    public const ROLE_GUEST = 'guest';
    public const ROLE_USER = 'user';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_ADMINISTRATOR = 'administrator';
    public const ROLE_OWNER = 'owner';


    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected UuidInterface|string $id;


    #[ORM\Column(type: 'string', length: 64, unique: true)]
    private string $name;

    #[ORM\Column(type: 'string', length: 20)]
    private string $scope;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?self $parent = null;

    /**
     * @var Collection<int, Role>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent')]
    private Collection $children;

    /**
     * @var Collection<int, UserRole>
     */
    #[ORM\OneToMany(targetEntity: UserRole::class, mappedBy: 'role', cascade: ['persist'], orphanRemoval: true)]
    private Collection $users;

    /**
     * @var Collection<int, RolePermission>
     */
    #[ORM\OneToMany(targetEntity: RolePermission::class, mappedBy: 'role', orphanRemoval: true)]
    private Collection $permissions;

    public function __construct(string $name, string $scope)
    {

        $this->id = Uuid::uuid4();
        $this->name = $name;
        $this->scope = $scope;

        $this->children    = new ArrayCollection();
        $this->users       = new ArrayCollection();
        $this->permissions = new ArrayCollection();

    }


    public function getAllowedRoles(): array
    {
        return [
            self::ROLE_GUEST,
            self::ROLE_USER,
            self::ROLE_MANAGER,
            self::ROLE_OWNER,
            self::ROLE_ADMINISTRATOR,
        ];
    }

    public function id(): UuidInterface
    {
        return $this->id;
    }

    public function addUser(User $user): void
    {
        foreach ($this->users as $userRole) {
            if ($userRole->getUser() === $user) {
                return;
            }
        }

        $userRole = new UserRole($user, $this);
        $this->users->add($userRole);
    }

    public function removeUser(User $user): void
    {
        foreach ($this->users as $userRole) {
            if ($userRole->getUser() === $user) {
                $this->users->removeElement($userRole);
                break;
            }
        }
    }


    public function addChild(self $child): void
    {
        $this->children->add($child);
    }

    public function removeChild(self $child): void
    {
        $this->children->removeElement($child);
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function scope(): RoleScope
    {
        return $this->scope;
    }


    public function name(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getRoleId(): string
    {
        return $this->name;
    }
}
