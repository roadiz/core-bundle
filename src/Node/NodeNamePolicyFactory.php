<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Node;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Bag\Settings;

final class NodeNamePolicyFactory
{
    private ManagerRegistry $registry;
    private Settings $settings;

    /**
     * @param ManagerRegistry $registry
     * @param Settings $settings
     */
    public function __construct(ManagerRegistry $registry, Settings $settings)
    {
        $this->registry = $registry;
        $this->settings = $settings;
    }

    public function create(): NodeNamePolicyInterface
    {
        return new NodeNameChecker(
            $this->registry,
            $this->settings->getBoolean('use_typed_node_names', false)
        );
    }
}
