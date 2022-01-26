<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;

/**
 * CustomFormField entities are used to create CustomForms with
 * custom data structure.
 *
 * @ORM\Entity(repositoryClass="RZ\Roadiz\CoreBundle\Repository\CustomFormFieldAttributeRepository")
 * @ORM\Table(name="custom_form_field_attributes", indexes={
 *     @ORM\Index(columns={"custom_form_answer_id", "custom_form_field_id"}, name="cffattribute_answer_field")
 * })
 * @ORM\HasLifecycleCallbacks
 */
class CustomFormFieldAttribute extends AbstractEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="RZ\Roadiz\CoreBundle\Entity\CustomFormAnswer", inversedBy="answerFields")
     * @ORM\JoinColumn(name="custom_form_answer_id", referencedColumnName="id", onDelete="CASCADE")
     * @var CustomFormAnswer|null
     */
    protected ?CustomFormAnswer $customFormAnswer = null;

    /**
     * @ORM\ManyToOne(targetEntity="RZ\Roadiz\CoreBundle\Entity\CustomFormField", inversedBy="customFormFieldAttributes")
     * @ORM\JoinColumn(name="custom_form_field_id", referencedColumnName="id", onDelete="CASCADE")
     * @var CustomFormField|null
     */
    protected ?CustomFormField $customFormField = null;

    /**
     * @var Collection<Document>
     * @ORM\ManyToMany(targetEntity="RZ\Roadiz\CoreBundle\Entity\Document", inversedBy="customFormFieldAttributes")
     * @ORM\JoinTable(name="custom_form_answers_documents", joinColumns={
     *     @ORM\JoinColumn(name="customformfieldattribute_id", onDelete="CASCADE")
     * })
     */
    protected Collection $documents;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    protected ?string $value = null;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }

    /**
     * @return string $value
     */
    public function getValue(): ?string
    {
        if ($this->getCustomFormField()->isDocuments()) {
            return implode(', ', $this->getDocuments()->map(function (Document $document) {
                return $document->getRelativePath();
            })->toArray());
        }
        return $this->value;
    }

    /**
     * @param string|null $value
     * @return $this
     */
    public function setValue(?string $value): CustomFormFieldAttribute
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Gets the value of customFormAnswer.
     *
     * @return CustomFormAnswer|null
     */
    public function getCustomFormAnswer(): ?CustomFormAnswer
    {
        return $this->customFormAnswer;
    }

    /**
     * Sets the value of customFormAnswer.
     *
     * @param CustomFormAnswer $customFormAnswer the custom form answer
     *
     * @return self
     */
    public function setCustomFormAnswer(CustomFormAnswer $customFormAnswer): CustomFormFieldAttribute
    {
        $this->customFormAnswer = $customFormAnswer;

        return $this;
    }

    /**
     * @return CustomFormField|null
     */
    public function getCustomFormField(): ?CustomFormField
    {
        return $this->customFormField;
    }

    /**
     * Sets the value of customFormField.
     *
     * @param CustomFormField $customFormField the custom form field
     * @return self
     */
    public function setCustomFormField(CustomFormField $customFormField): CustomFormFieldAttribute
    {
        $this->customFormField = $customFormField;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    /**
     * @param Collection $documents
     *
     * @return CustomFormFieldAttribute
     */
    public function setDocuments(Collection $documents): CustomFormFieldAttribute
    {
        $this->documents = $documents;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getValue() ?? '';
    }
}
