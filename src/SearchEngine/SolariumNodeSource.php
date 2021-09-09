<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesIndexingEvent;
use RZ\Roadiz\Markdown\MarkdownInterface;
use Solarium\Client;
use Solarium\QueryType\Update\Query\Query;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Wrap a Solarium and a NodeSource together to ease indexing.
 */
class SolariumNodeSource extends AbstractSolarium
{
    public const DOCUMENT_TYPE = 'NodesSources';
    public const IDENTIFIER_KEY = 'node_source_id_i';

    protected NodesSources $nodeSource;
    protected EventDispatcherInterface $dispatcher;

    /**
     * @param NodesSources             $nodeSource
     * @param ClientRegistry $clientRegistry
     * @param EventDispatcherInterface $dispatcher
     * @param LoggerInterface     $logger
     * @param MarkdownInterface   $markdown
     */
    public function __construct(
        NodesSources $nodeSource,
        ClientRegistry $clientRegistry,
        EventDispatcherInterface $dispatcher,
        LoggerInterface $logger = null,
        MarkdownInterface $markdown = null
    ) {
        parent::__construct($clientRegistry, $logger, $markdown);
        $this->nodeSource = $nodeSource;
        $this->dispatcher = $dispatcher;
    }

    public function getDocumentId()
    {
        return $this->nodeSource->getId();
    }

    /**
     * Get a key/value array representation of current node-source document.
     *
     * @param bool $subResource Tell when this field gathering is for a main resource indexation or a sub-resource
     *
     * @return array
     * @throws \Exception
     */
    public function getFieldsAssoc(bool $subResource = false): array
    {
        $event = new NodesSourcesIndexingEvent($this->nodeSource, [], $this);

        return $this->dispatcher->dispatch($event)->getAssociations();
    }

    /**
     * Remove any document linked to current node-source.
     *
     * @param Query $update
     * @return boolean
     */
    public function clean(Query $update)
    {
        $update->addDeleteQuery(
            static::IDENTIFIER_KEY . ':"' . $this->nodeSource->getId() . '"' .
            '&'.static::TYPE_DISCRIMINATOR.':"' . static::DOCUMENT_TYPE . '"' .
            '&locale_s:"' . $this->nodeSource->getTranslation()->getLocale() . '"'
        );

        return true;
    }
}
