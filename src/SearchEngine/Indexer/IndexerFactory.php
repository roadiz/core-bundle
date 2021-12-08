<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine\Indexer;

use LogicException;
use Psr\Container\ContainerInterface;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Tag;

class IndexerFactory
{
    protected ContainerInterface $serviceLocator;

    /**
     * @param ContainerInterface $serviceLocator
     */
    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @param class-string $classname
     * @return Indexer
     */
    public function getIndexerFor(string $classname): Indexer
    {
        switch ($classname) {
            case Node::class:
                return $this->serviceLocator->get(NodeIndexer::class);
            case NodesSources::class:
                return $this->serviceLocator->get(NodesSourcesIndexer::class);
            case Document::class:
                return $this->serviceLocator->get(DocumentIndexer::class);
            case Tag::class:
                return $this->serviceLocator->get(TagIndexer::class);
            case Folder::class:
                return $this->serviceLocator->get(FolderIndexer::class);
            default:
                throw new LogicException(sprintf('No indexer found for "%s"', $classname));
        }
    }
}
