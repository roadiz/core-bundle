<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter as BaseFilter;

/**
 * @ORM\Entity(repositoryClass="RZ\Roadiz\CoreBundle\Repository\RealmRepository")
 * @ORM\Table(name="realms", indexes={
 *     @ORM\Index(columns={"type"}, name="realms_type"),
 *     @ORM\Index(columns={"behaviour"}, name="realms_behaviour"),
 * })
 * @ApiFilter(ApiPlatform\Core\Serializer\Filter\PropertyFilter::class)
 * @ApiFilter(BaseFilter\SearchFilter::class, properties={
 *     "type": "exact",
 *     "behaviour": "exact",
 *     "name": "exact"
 * })
 */
class Realm extends AbstractEntity implements RealmInterface
{
    /**
     * @var string
     * @ORM\Column(name="type", type="string", length=30)
     * @Serializer\Groups({"get", "realm"})
     * @SymfonySerializer\Groups({"get", "realm"})
     */
    private string $type = RealmInterface::TYPE_PLAIN_PASSWORD;
    /**
     * @var string
     * @ORM\Column(name="behaviour", type="string", length=30, nullable=false, options={"default"="none"})
     * @Serializer\Groups({"get", "realm", "web_response"})
     * @SymfonySerializer\Groups({"get", "realm", "web_response"})
     */
    private string $behaviour = RealmInterface::BEHAVIOUR_NONE;
    /**
     * @var string
     * @ORM\Column(name="name", unique=true)
     * @Serializer\Groups({"get", "realm", "web_response"})
     * @SymfonySerializer\Groups({"get", "realm", "web_response"})
     */
    private string $name = '';
    /**
     * @var string|null
     * @ORM\Column(name="plain_password", unique=false, type="string", length=255, nullable=true)
     * @Serializer\Exclude()
     * @SymfonySerializer\Ignore()
     */
    private ?string $plainPassword = null;
    /**
     * @var Role|null
     * @ORM\ManyToOne(targetEntity="Role")
     * @ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="SET NULL")
     * @Serializer\Exclude()
     * @SymfonySerializer\Ignore()
     */
    private ?Role $roleEntity = null;
    /**
     * @var string|null
     * @ORM\Column(name="serialization_group", type="string", length=200, nullable=true)
     * @Serializer\Exclude()
     * @SymfonySerializer\Ignore()
     */
    private ?string $serializationGroup = null;
    /**
     * @var Collection<UserInterface>|ArrayCollection<UserInterface>
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="realms_users")
     * @Serializer\Exclude()
     * @SymfonySerializer\Ignore()
     */
    private Collection $users;
    /**
     * @var Collection<RealmNode>|ArrayCollection<RealmNode>
     * @ORM\OneToMany(targetEntity="RealmNode", mappedBy="realm")
     * @Serializer\Exclude()
     * @SymfonySerializer\Ignore()
     */
    private Collection $realmNodes;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->realmNodes = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Realm
     */
    public function setType(string $type): Realm
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name ?? '';
    }

    /**
     * @param string $name
     * @return Realm
     */
    public function setName(string $name): Realm
    {
        $this->name = $name;
        if (null === $this->serializationGroup) {
            $this->serializationGroup = (new AsciiSlugger())->slug($this->name, '_')->lower()->toString();
        }
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRole(): ?string
    {
        if (null === $this->roleEntity) {
            return null;
        }
        return $this->roleEntity->getRole();
    }

    /**
     * @return Role|null
     */
    public function getRoleEntity(): ?Role
    {
        return $this->roleEntity;
    }

    /**
     * @param Role|null $roleEntity
     * @return Realm
     */
    public function setRoleEntity(?Role $roleEntity): Realm
    {
        $this->roleEntity = $roleEntity;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSerializationGroup(): ?string
    {
        return $this->serializationGroup;
    }

    /**
     * @param string|null $serializationGroup
     * @return Realm
     */
    public function setSerializationGroup(?string $serializationGroup): Realm
    {
        $this->serializationGroup = null !== $serializationGroup ?
            (new AsciiSlugger())->slug($serializationGroup, '_')->lower()->toString() :
            (new AsciiSlugger())->slug($this->getName(), '_')->lower()->toString();
        return $this;
    }

    /**
     * @return ArrayCollection<RealmNode>|Collection<RealmNode>
     */
    public function getRealmNodes(): Collection
    {
        return $this->realmNodes;
    }

    /**
     * @param ArrayCollection<RealmNode>|Collection<RealmNode> $realmNodes
     * @return Realm
     */
    public function setRealmNodes(Collection $realmNodes)
    {
        $this->realmNodes = $realmNodes;
        return $this;
    }

    /**
     * @return Collection<UserInterface>|ArrayCollection<UserInterface>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @param Collection<UserInterface>|ArrayCollection<UserInterface> $users
     * @return Realm
     */
    public function setUsers(Collection $users)
    {
        $this->users = $users;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     * @return Realm
     */
    public function setPlainPassword(?string $plainPassword): Realm
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    /**
     * @return string
     */
    public function getBehaviour(): string
    {
        return $this->behaviour;
    }

    /**
     * @param string $behaviour
     * @return Realm
     */
    public function setBehaviour(string $behaviour): Realm
    {
        $this->behaviour = $behaviour;
        return $this;
    }

    /**
     * @return string
     * @Serializer\Groups({"get", "realm", "web_response"})
     * @SymfonySerializer\Groups({"get", "realm", "web_response"})
     */
    public function getAuthenticationScheme(): string
    {
        switch ($this->getType()) {
            case RealmInterface::TYPE_PLAIN_PASSWORD:
                return 'PasswordQuery';
            default:
                return 'Bearer';
        }
    }

    public function getChallenge(): string
    {
        return $this->getAuthenticationScheme() . ' realm="' . addslashes($this->getName()) . '"';
    }
}
