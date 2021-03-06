<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * A group gather User and Roles.
 *
 * @ORM\Entity(repositoryClass="RZ\Roadiz\CoreBundle\Repository\GroupRepository")
 * @ORM\Table(name="usergroups")
 */
class Group extends AbstractEntity
{
    /**
     * @ORM\Column(type="string", unique=true)
     * @Serializer\Groups({"user", "role", "group"})
     * @SymfonySerializer\Groups({"user", "role", "group"})
     * @Serializer\Type("string")
     * @var string
     */
    private string $name = '';
    /**
     * @ORM\ManyToMany(targetEntity="RZ\Roadiz\CoreBundle\Entity\User", mappedBy="groups")
     * @Serializer\Groups({"group_user"})
     * @SymfonySerializer\Groups({"group_user"})
     * @Serializer\Type("ArrayCollection<RZ\Roadiz\CoreBundle\Entity\User>")
     * @var Collection<User>
     */
    private Collection $users;
    /**
     * @ORM\ManyToMany(targetEntity="RZ\Roadiz\CoreBundle\Entity\Role", inversedBy="groups", cascade={"persist", "merge"})
     * @ORM\JoinTable(name="groups_roles",
     *      joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     * )
     * @var Collection<Role>
     * @Serializer\Groups({"group"})
     * @SymfonySerializer\Groups({"group"})
     * @Serializer\Type("ArrayCollection<RZ\Roadiz\CoreBundle\Entity\Role>")
     */
    private Collection $roleEntities;
    /**
     * @var array|null
     * @Serializer\Groups({"group", "user"})
     * @SymfonySerializer\Groups({"group", "user"})
     * @Serializer\Type("array<string>")
     */
    private ?array $roles = null;

    /**
     * Create a new Group.
     */
    public function __construct()
    {
        $this->roleEntities = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * Get roles names as a simple array.
     *
     * @return string[]
     */
    public function getRoles(): array
    {
        if ($this->roles === null) {
            $this->roles = array_map(function (Role $role) {
                return $role->getRole();
            }, $this->getRolesEntities()->toArray());
        }

        return $this->roles;
    }

    /**
     * Get roles entities.
     *
     * @return Collection
     */
    public function getRolesEntities(): ?Collection
    {
        return $this->roleEntities;
    }

    /**
     * Get roles entities.
     *
     * @param Collection $roles
     *
     * @return Group
     */
    public function setRolesEntities(Collection $roles): self
    {
        $this->roleEntities = $roles;
        /** @var Role $role */
        foreach ($this->roleEntities as $role) {
            $role->addGroup($this);
        }
        return $this;
    }

    /**
     * @param Role $role
     *
     * @return $this
     */
    public function addRoleEntity(Role $role): Group
    {
        if (!$this->roleEntities->contains($role)) {
            $this->roleEntities->add($role);
        }

        return $this;
    }

    /**
     * @param Role $role
     * @return $this
     * @deprecated Use addRoleEntity
     */
    public function addRole(Role $role): Group
    {
        return $this->addRoleEntity($role);
    }

    /**
     * @param Role $role
     *
     * @return $this
     */
    public function removeRoleEntity(Role $role): Group
    {
        if ($this->roleEntities->contains($role)) {
            $this->roleEntities->removeElement($role);
        }

        return $this;
    }

    /**
     * @param Role $role
     * @return $this
     * @deprecated Use removeRoleEntity
     */
    public function removeRole(Role $role): Group
    {
        return $this->removeRoleEntity($role);
    }
}
