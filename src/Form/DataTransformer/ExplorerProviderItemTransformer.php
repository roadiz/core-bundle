<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\DataTransformer;

use RZ\Roadiz\CoreBundle\Explorer\ExplorerItemInterface;
use RZ\Roadiz\CoreBundle\Explorer\ExplorerProviderInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ExplorerProviderItemTransformer implements DataTransformerInterface
{
    protected ExplorerProviderInterface $explorerProvider;

    /**
     * @param ExplorerProviderInterface $explorerProvider
     */
    public function __construct(ExplorerProviderInterface $explorerProvider)
    {
        $this->explorerProvider = $explorerProvider;
    }

    /**
     * @inheritDoc
     */
    public function transform($value)
    {
        if (!empty($value) && $this->explorerProvider->supports($value)) {
            $item = $this->explorerProvider->toExplorerItem($value);
            if (!$item instanceof ExplorerItemInterface) {
                throw new TransformationFailedException('Cannot transform model to ExplorerItem.');
            }
            return [$item];
        } elseif (!empty($value) && is_array($value)) {
            $idArray = [];
            foreach ($value as $entity) {
                if ($this->explorerProvider->supports($entity)) {
                    $item = $this->explorerProvider->toExplorerItem($entity);
                    if (!$item instanceof ExplorerItemInterface) {
                        throw new TransformationFailedException('Cannot transform model to ExplorerItem.');
                    }
                    $idArray[] = $item;
                } else {
                    throw new TransformationFailedException('Cannot transform model to ExplorerItem.');
                }
            }

            return array_filter($idArray);
        }
        return '';
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($value)
    {
        $items = $this->explorerProvider->getItemsById($value);
        $originals = [];
        foreach ($items as $item) {
            $originals[] = $item->getOriginal();
        }

        return array_filter($originals);
    }
}
