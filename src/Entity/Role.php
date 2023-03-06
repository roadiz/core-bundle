<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Repository\RoleRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Roles are persisted version of string Symfony's roles.
 */
#[
    ORM\Entity(repositoryClass: RoleRepository::class),
    ORM\Table(name: "roles"),
    UniqueEntity(fields: ["name"])
]
class Role implements PersistableInterface
{
    public const ROLE_DEFAULT = 'ROLE_USER';
    public const ROLE_SUPERADMIN = 'ROLE_SUPERADMIN';
    public const ROLE_BACKEND_USER = 'ROLE_BACKEND_USER';

    #[
        ORM\Id,
        ORM\Column(type: "integer"),
        ORM\GeneratedValue(strategy: "AUTO")
    ]
    protected ?int $id = null;

    #[ORM\Column(type: 'string', unique: true)]
    #[SymfonySerializer\Groups(['user', 'role', 'group'])]
    #[Serializer\Groups(['user', 'role', 'group'])]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '#^ROLE_([A-Z0-9\_]+)$#', message: 'role.name.must_comply_with_standard')]
    #[Assert\Length(max: 250)]
    private string $name = '';

    /**
     * @var Collection<Group>
     */
    #[ORM\ManyToMany(targetEntity: Group::class, mappedBy: 'roleEntities', cascade: ['persist', 'merge'])]
    #[SymfonySerializer\Groups(['role'])]
    #[Serializer\Groups(['role'])]
    #[Serializer\Accessor(getter: "getGroups", setter: "setGroups")]
    #[Serializer\Type("ArrayCollection<RZ\Roadiz\CoreBundle\Entity\Group>")]
    private Collection $groups;

    /**
     * Create a new Role with its string representation.
     *
     * @param string $name Role name
     */
    public function __construct(string $name)
    {
        $this->setRole($name);
        $this->groups = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return Role
     */
    public function setId(?int $id): Role
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     * @deprecated Use getRole method
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Role
     * @deprecated Use setRole method
     */
    public function setName(string $name): Role
    {
        return $this->setRole($name);
    }

    /**
     * @param string $role
     * @return Role
     */
    public function setRole(string $role): Role
    {
        $this->name = static::cleanName($role);
        return $this;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public static function cleanName(string $name): string
    {
        $string = (new UnicodeString($name))
            ->ascii()
            ->folded()
            ->snake()
            ->lower()
        ;
        if (!$string->startsWith('role_')) {
            $string = $string->prepend('role_');
        }

        return $string->upper()->toString();
    }

    /**
     * @param Group $group
     * @return $this
     */
    public function addGroup(Group $group): Role
    {
        if (!$this->getGroups()->contains($group)) {
            $this->getGroups()->add($group);
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    /**
     * @param Collection $groups
     * @return $this
     */
    public function setGroups(Collection $groups): Role
    {
        $this->groups = $groups;
        /** @var Group $group */
        foreach ($this->groups as $group) {
            $group->addRoleEntity($this);
        }

        return $this;
    }

    /**
     * @param Group $group
     * @return $this
     */
    public function removeGroup(Group $group): Role
    {
        if ($this->getGroups()->contains($group)) {
            $this->getGroups()->removeElement($group);
        }

        return $this;
    }

    /**
     * Get a classified version of current role name.
     *
     * It replaces underscores by dashes and lowercase.
     *
     * @return string
     * @Serializer\Groups({"role"})
     */
    #[SymfonySerializer\Groups(['role'])]
    public function getClassName(): string
    {
        return str_replace('_', '-', strtolower($this->getRole()));
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function required(): bool
    {
        if (
            $this->getRole() == static::ROLE_DEFAULT ||
            $this->getRole() == static::ROLE_SUPERADMIN ||
            $this->getRole() == static::ROLE_BACKEND_USER
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getRole();
    }
}
