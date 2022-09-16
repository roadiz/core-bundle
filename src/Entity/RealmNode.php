<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;

/**
 * @ORM\Entity(repositoryClass="RZ\Roadiz\CoreBundle\Repository\RealmNodeRepository")
 * @ORM\Table(name="realms_nodes", indexes={
 *     @ORM\Index(name="realms_nodes_inheritance_type", columns={"inheritance_type"}),
 *     @ORM\Index(name="realms_nodes_realm", columns={"realm_id"}),
 *     @ORM\Index(name="realms_nodes_node", columns={"node_id"}),
 *     @ORM\Index(name="realms_nodes_node_inheritance_type", columns={"node_id", "inheritance_type"})
 * }, uniqueConstraints={
 *     @ORM\UniqueConstraint(name="realms_nodes_unique", columns={"node_id", "realm_id"}),
 *  })
 * @UniqueEntity(fields={"node", "realm"})
 */
class RealmNode extends AbstractEntity
{
    /**
     * @var Node
     * @ORM\ManyToOne(targetEntity="Node")
     * @ORM\JoinColumn(name="node_id", referencedColumnName="id", unique=false, nullable=false, onDelete="CASCADE")
     * @Serializer\Exclude()
     * @SymfonySerializer\Ignore()
     */
    private Node $node;
    /**
     * @var Realm|null
     * @ORM\ManyToOne(targetEntity="Realm", inversedBy="realmNodes")
     * @ORM\JoinColumn(name="realm_id", referencedColumnName="id", unique=false, nullable=true, onDelete="CASCADE")
     * @Serializer\Exclude()
     * @SymfonySerializer\Ignore()
     */
    private ?Realm $realm = null;
    /**
     * @ORM\Column(name="inheritance_type", type="string", length=10, nullable=false)
     * @Serializer\Exclude()
     * @SymfonySerializer\Ignore()
     */
    private string $inheritanceType = RealmInterface::INHERITANCE_AUTO;

    /**
     * @return Node
     */
    public function getNode(): Node
    {
        return $this->node;
    }

    /**
     * @param Node $node
     * @return RealmNode
     */
    public function setNode(Node $node): RealmNode
    {
        $this->node = $node;
        return $this;
    }

    /**
     * @return Realm|null
     */
    public function getRealm(): ?Realm
    {
        return $this->realm;
    }

    /**
     * @param Realm|null $realm
     * @return RealmNode
     */
    public function setRealm(?Realm $realm): RealmNode
    {
        $this->realm = $realm;
        return $this;
    }

    /**
     * @return string
     */
    public function getInheritanceType(): string
    {
        return $this->inheritanceType;
    }

    /**
     * @param string $inheritanceType
     * @return RealmNode
     */
    public function setInheritanceType(string $inheritanceType): RealmNode
    {
        $this->inheritanceType = $inheritanceType;
        return $this;
    }
}
