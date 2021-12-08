<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\DataTransformer;

use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerProviderInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ProviderDataTransformer implements DataTransformerInterface
{
    protected NodeTypeField $nodeTypeField;
    protected ExplorerProviderInterface $provider;

    /**
     * @param NodeTypeField             $nodeTypeField
     * @param ExplorerProviderInterface $provider
     */
    public function __construct(NodeTypeField $nodeTypeField, ExplorerProviderInterface $provider)
    {
        $this->nodeTypeField = $nodeTypeField;
        $this->provider = $provider;
    }

    /**
     * @param mixed $entitiesToForm
     * @return mixed
     */
    public function transform($entitiesToForm)
    {
        if ($this->nodeTypeField->isMultiProvider() && (null === $entitiesToForm || is_array($entitiesToForm))) {
            if (null !== $entitiesToForm && count($entitiesToForm) > 0) {
                return $this->provider->getItemsById($entitiesToForm);
            }
            return [];
        } elseif ($this->nodeTypeField->isSingleProvider()) {
            if (isset($entitiesToForm)) {
                return $this->provider->getItemsById($entitiesToForm);
            }
            return null;
        }
        throw new TransformationFailedException('Provider entities cannot be transformed to form model.');
    }

    /**
     * @param mixed $formToEntities
     * @return mixed
     */
    public function reverseTransform($formToEntities)
    {
        if (
            is_array($formToEntities) &&
            $this->nodeTypeField->isSingleProvider() &&
            isset($formToEntities[0])
        ) {
            return $formToEntities[0];
        }

        return $formToEntities;
    }
}
