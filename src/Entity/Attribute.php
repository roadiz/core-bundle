<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\CoreBundle\Model\AttributeInterface;
use RZ\Roadiz\CoreBundle\Model\AttributeTrait;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;

/**
 * @package RZ\Roadiz\CoreBundle\Entity
 * @ORM\Entity(repositoryClass="RZ\Roadiz\CoreBundle\Repository\EntityRepository")
 * @ORM\Table(name="attributes", indexes={
 *     @ORM\Index(columns={"code"}),
 *     @ORM\Index(columns={"type"}),
 *     @ORM\Index(columns={"searchable"}),
 *     @ORM\Index(columns={"group_id"})
 * })
 * @ORM\HasLifecycleCallbacks
 */
class Attribute extends AbstractEntity implements AttributeInterface
{
    use AttributeTrait;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RZ\Roadiz\CoreBundle\Entity\AttributeDocuments",
     *     mappedBy="attribute",
     *     orphanRemoval=true,
     *     cascade={"persist", "merge"}
     * )
     * @ORM\OrderBy({"position" = "ASC"})
     * @var Collection<AttributeDocuments>
     * @Serializer\Exclude
     * @Serializer\Type("ArrayCollection<RZ\Roadiz\CoreBundle\Entity\AttributeDocuments>")
     */
    protected Collection $attributeDocuments;

    public function __construct()
    {
        $this->attributeTranslations = new ArrayCollection();
        $this->attributeValues = new ArrayCollection();
        $this->attributeDocuments = new ArrayCollection();
    }

    /**
     * @return Collection<AttributeDocuments>
     */
    public function getAttributeDocuments(): Collection
    {
        return $this->attributeDocuments;
    }

    /**
     * @param Collection $attributeDocuments
     *
     * @return Attribute
     */
    public function setAttributeDocuments(Collection $attributeDocuments): Attribute
    {
        $this->attributeDocuments = $attributeDocuments;

        return $this;
    }

    /**
     * @return Collection<Document>
     * @Serializer\SerializedName("documents")
     * @Serializer\VirtualProperty()
     * @Serializer\Groups({"attribute", "node", "nodes_sources"})
     */
    public function getDocuments(): Collection
    {
        /** @var Collection<Document> $values */
        $values = $this->attributeDocuments->map(function (AttributeDocuments $attributeDocuments) {
            return $attributeDocuments->getDocument();
        })->filter(function (?Document $document) {
            return null !== $document;
        });
        return $values; // phpstan does not understand filtering null values
    }
}
