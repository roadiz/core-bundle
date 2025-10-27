<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\NodeType;

use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\DependencyInjection\Configuration;
use RZ\Roadiz\EntityGenerator\Field\DefaultValuesResolverInterface;

final readonly class DefaultValuesResolver implements DefaultValuesResolverInterface
{
    public function __construct(
        private NodeTypes $nodeTypesBag,
        private string $inheritanceType,
    ) {
    }

    #[\Override]
    public function getDefaultValuesAmongAllFields(NodeTypeFieldInterface $field): array
    {
        /*
         * With joined inheritance, we can use current field default values because
         * SQL field won't be shared between all node types.
         */
        if (Configuration::INHERITANCE_TYPE_JOINED === $this->inheritanceType) {
            return array_map('trim', $field->getDefaultValuesAsArray());
        } else {
            /*
             * With single table inheritance, we need to get all default values
             * from all fields of all node types.
             */
            $defaultValues = [];
            $nodeTypeFields = [];
            $nodeTypes = $this->nodeTypesBag->all();
            foreach ($nodeTypes as $nodeType) {
                $nodeTypeFields = [
                    ...$nodeTypeFields,
                    ...$nodeType->getFields()->filter(fn (NodeTypeFieldInterface $nodeTypeField) => $nodeTypeField->getName() === $field->getName() && $nodeTypeField->getType() === $field->getType())->toArray(),
                ];
            }
            foreach ($nodeTypeFields as $nodeTypeField) {
                $values = array_filter(array_map('trim', $nodeTypeField->getDefaultValuesAsArray()));
                $defaultValues = array_merge($defaultValues, $values);
            }

            return $defaultValues;
        }
    }

    #[\Override]
    public function getMaxDefaultValuesLengthAmongAllFields(NodeTypeFieldInterface $field): int
    {
        // get max length of exploded default values
        $max = 0;
        foreach ($this->getDefaultValuesAmongAllFields($field) as $value) {
            $max = max($max, \mb_strlen((string) $value));
        }

        return $max > 0 ? $max : 250;
    }
}
