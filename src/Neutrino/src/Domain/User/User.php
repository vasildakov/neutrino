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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Mezzio\Authentication\UserInterface;
use Neutrino\Repository\UserRepository;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: "users")]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected UuidInterface|string $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $avatar = null;

    /**
     * @var Collection<int, UserRole>
     */
    #[ORM\OneToMany(targetEntity: UserRole::class, mappedBy: 'user', cascade: ['persist'], orphanRemoval: true)]
    private Collection $roles;

    #[ORM\Column(name: 'createdAt', type: Types::DATETIME_IMMUTABLE, nullable: false,)]
    private DateTimeImmutable $createdAt;

    public function __construct(
        #[ORM\Column(name: 'email', type: 'email', length: 255, nullable: false,)]
        private readonly Email $email,

        #[ORM\Column(name: 'password', type: 'password', length: 255, nullable: false,)]
        private readonly Password $password,
    ) {
        $this->id = Uuid::uuid4();
        $this->roles = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): UuidInterface|string
    {
        return $this->id;
    }

    public function getShortId(): string
    {

        return substr($this->id->getHex()->toString(), 0, 8);

    }

    /**
     * @return Email
     */
    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getName(): string
    {
        return '';
    }

    /**
     * @return Password
     */
    public function getPassword(): Password
    {
        return $this->password;
    }

    public function setAvatar(?string $avatar): void
    {
        $this->avatar = $avatar;

    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }


    public function getIdentity(): string
    {
        return (string) $this->getEmail();
    }

    public function addRole(Role $role): void
    {
        foreach ($this->roles as $userRole) {
            if ($userRole->getRole() === $role) {
                return;
            }
        }

        $userRole = new UserRole($this, $role);
        $this->roles->add($userRole);
    }


    public function removeRole(Role $role): void
    {
        foreach ($this->roles as $userRole) {
            if ($userRole->getRole() === $role) {
                $this->roles->removeElement($userRole);
                break;
            }
        }
    }

    /**
     * @return Role[]
     */
    public function getRoles(): iterable
    {
        return $this->roles->map(fn(UserRole $userRole) => $userRole->getRole());
    }

    /**
     * @param string $name
     * @param $default
     * @return mixed|string|null
     */
    public function getDetail(string $name, $default = null): mixed
    {
        return match ($name) {
            'id'       => (string) $this->getId(),
            'email'    => (string) $this->getEmail(),
            'platform' => $this->hasPlatformRole(),
            'avatar'   => $this->getAvatar(),
            default    => $default,
        };
    }


    public function hasPlatformRole(): bool
    {
        foreach ($this->roles as $userRole) {
            if ($userRole->getRole()->isPlatformScope()) {
                return true;
            }
        }
        return false;
    }

    private function getScope(): string
    {
        return $this->hasPlatformRole() ? Role::SCOPE_PLATFORM : Role::SCOPE_DASHBOARD;
    }

    /**
     * @return array
     */
    public function getDetails(): array
    {
        return [
            'id'       => (string) $this->getId(),
            'email'    => (string) $this->getEmail(),
            'scope'    => $this->getScope(),
            'platform' => $this->hasPlatformRole(),
            'avatar'   => $this->getAvatar(),
        ];
    }

    public function getRolesNames(): array
    {
        return $this->roles->map(
            fn(UserRole $userRole) => $userRole->getRole()->name()
        )->toArray();
    }
}
