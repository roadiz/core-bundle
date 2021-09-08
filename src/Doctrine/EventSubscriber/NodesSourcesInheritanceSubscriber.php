<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\DependencyInjection\Configuration;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeType;

/**
 * @package RZ\Roadiz\Core\Events
 */
class NodesSourcesInheritanceSubscriber implements EventSubscriber
{
    private NodeTypes $nodeTypes;
    private string $inheritanceType;

    /**
     * @param NodeTypes $nodeTypes
     * @param string $inheritanceType
     */
    public function __construct(NodeTypes $nodeTypes, string $inheritanceType)
    {
        $this->nodeTypes = $nodeTypes;
        $this->inheritanceType = $inheritanceType;
    }


    /**
     * @inheritDoc
     */
    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
        ];
    }

    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        // the $metadata is all the mapping info for this class
        $metadata = $eventArgs->getClassMetadata();
        // the annotation reader accepts a ReflectionClass, which can be
        // obtained from the $metadata
        $class = $metadata->getReflectionClass();

        if ($class->getName() === NodesSources::class) {
            try {
                /** @var NodeType[] $nodeTypes */
                $nodeTypes = $this->nodeTypes->all();
                $map = [];
                foreach ($nodeTypes as $type) {
                    $map[strtolower($type->getName())] = $type->getSourceEntityFullQualifiedClassName();
                }
                $metadata->setDiscriminatorMap($map);

                if ($this->inheritanceType === Configuration::INHERITANCE_TYPE_JOINED) {
                    $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_JOINED);
                } elseif ($this->inheritanceType === Configuration::INHERITANCE_TYPE_SINGLE_TABLE) {
                    $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_SINGLE_TABLE);
                } else {
                    throw new \RuntimeException('Inheritance type not supported: ' . $this->inheritanceType);
                }
            } catch (\Exception $e) {
                /*
                 * Database tables don't exist yet
                 * Need Install
                 */
            }
        }
    }
}
