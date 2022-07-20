<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Node;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesCreatedEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class NodeTranslator
{
    private ManagerRegistry $managerRegistry;
    private EventDispatcherInterface $dispatcher;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(ManagerRegistry $managerRegistry, EventDispatcherInterface $dispatcher)
    {
        $this->managerRegistry = $managerRegistry;
        $this->dispatcher = $dispatcher;
    }

    public function translateNode(Translation $translation, Node $node, bool $translateChildren = false): Node
    {
        $this->translateSingleNode($translation, $node);

        if ($translateChildren) {
            /** @var Node $child */
            foreach ($node->getChildren() as $child) {
                $this->translateNode($translation, $child, $translateChildren);
            }
        }

        return $node;
    }

    private function translateSingleNode(Translation $translation, Node $node): NodesSources
    {
        /** @var NodesSources|null $existing */
        $existing = $this->managerRegistry
            ->getRepository(NodesSources::class)
            ->setDisplayingAllNodesStatuses(true)
            ->setDisplayingNotPublishedNodes(true)
            ->findOneByNodeAndTranslation($node, $translation);

        if (null === $existing) {
            /** @var NodesSources|false $baseSource */
            $baseSource = $node->getNodeSources()->filter(function (NodesSources $nodesSources) {
                return $nodesSources->getTranslation()->isDefaultTranslation();
            })->first() ?: $node->getNodeSources()->first();

            if (!($baseSource instanceof NodesSources)) {
                throw new \RuntimeException('Cannot translate a Node without any NodesSources');
            }
            $source = clone $baseSource;
            $this->managerRegistry->getManager()->persist($source);

            foreach ($source->getDocumentsByFields() as $document) {
                $this->managerRegistry->getManager()->persist($document);
            }
            $source->setTranslation($translation);
            $source->setNode($node);

            /*
             * Dispatch event
             */
            $this->dispatcher->dispatch(new NodesSourcesCreatedEvent($source));

            return $source;
        } else {
            return $existing;
        }
    }
}
