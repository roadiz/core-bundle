<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use Symfony\Component\String\UnicodeString;

/**
 * Roles are persisted version of string Symfony's roles.
 *
 * @ORM\Entity(repositoryClass="RZ\Roadiz\CoreBundle\Repository\RoleRepository")
 * @ORM\Table(name="roles")
 */
class Role implements PersistableInterface
{
    public const ROLE_DEFAULT = 'ROLE_USER';
    public const ROLE_SUPERADMIN = 'ROLE_SUPERADMIN';
    public const ROLE_BACKEND_USER = 'ROLE_BACKEND_USER';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Role
     */
    public function setId($id): Role
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @ORM\Column(type="string", unique=true)
     * @Serializer\Groups({"user", "role", "group"})
     * @SymfonySerializer\Groups({"user", "role", "group"})
     * @Serializer\Type("string")
     * @var string
     */
    private string $name = '';

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
     * @return string
     */
    public function getRole(): string
    {
        return $this->name;
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
     * @ORM\ManyToMany(
     *     targetEntity="RZ\Roadiz\CoreBundle\Entity\Group",
     *     mappedBy="roles",
     *     cascade={"persist", "merge"}
     * )
     * @Serializer\Groups({"role"})
     * @SymfonySerializer\Groups({"role"})
     * @Serializer\Type("ArrayCollection<RZ\Roadiz\CoreBundle\Entity\Group>")
     * @Serializer\Accessor(getter="getGroups", setter="setGroups")
     * @var Collection<Group>
     */
    private Collection $groups;

    /**
     * @return Collection
     */
    public function getGroups(): Collection
    {
        return $this->groups;
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
     * @param Collection $groups
     * @return $this
     */
    public function setGroups(Collection $groups): Role
    {
        $this->groups = $groups;
        /** @var Group $group */
        foreach ($this->groups as $group) {
            $group->addRole($this);
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
     * @SymfonySerializer\Groups({"role"})
     */
    public function getClassName(): string
    {
        return str_replace('_', '-', strtolower($this->getRole()));
    }

    /**
     * @return bool
     */
    public function required(): bool
    {
        if ($this->getRole() == static::ROLE_DEFAULT ||
            $this->getRole() == static::ROLE_SUPERADMIN ||
            $this->getRole() == static::ROLE_BACKEND_USER) {
            return true;
        }

        return false;
    }

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
     * @return string
     * @SymfonySerializer\Ignore
     */
    public function __toString(): string
    {
        return $this->getRole();
    }
}
