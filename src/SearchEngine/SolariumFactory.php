<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Exception\SolrServerNotConfiguredException;
use RZ\Roadiz\CoreBundle\EntityHandler\HandlerFactoryInterface;
use RZ\Roadiz\Markdown\MarkdownInterface;
use Solarium\Client;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SolariumFactory implements SolariumFactoryInterface
{
    protected ?Client $solr;
    protected LoggerInterface $logger;
    protected MarkdownInterface $markdown;
    protected EventDispatcherInterface $dispatcher;
    protected HandlerFactoryInterface $handlerFactory;

    /**
     * @param Client|null $solr
     * @param LoggerInterface|null $logger
     * @param MarkdownInterface $markdown
     * @param EventDispatcherInterface $dispatcher
     * @param HandlerFactoryInterface $handlerFactory
     */
    public function __construct(
        ?Client $solr,
        ?LoggerInterface $logger,
        MarkdownInterface $markdown,
        EventDispatcherInterface $dispatcher,
        HandlerFactoryInterface $handlerFactory
    ) {
        $this->solr = $solr;
        $this->logger = $logger ?? new NullLogger();
        $this->markdown = $markdown;
        $this->dispatcher = $dispatcher;
        $this->handlerFactory = $handlerFactory;
    }

    public function createWithDocument(Document $document): SolariumDocument
    {
        if (null === $this->solr) {
            throw new SolrServerNotConfiguredException();
        }
        return new SolariumDocument(
            $document,
            $this,
            $this->solr,
            $this->logger,
            $this->markdown
        );
    }

    public function createWithDocumentTranslation(DocumentTranslation $documentTranslation): SolariumDocumentTranslation
    {
        if (null === $this->solr) {
            throw new SolrServerNotConfiguredException();
        }
        return new SolariumDocumentTranslation(
            $documentTranslation,
            $this->solr,
            $this->logger,
            $this->markdown
        );
    }

    public function createWithNodesSources(NodesSources $nodeSource): SolariumNodeSource
    {
        if (null === $this->solr) {
            throw new SolrServerNotConfiguredException();
        }
        return new SolariumNodeSource(
            $nodeSource,
            $this->solr,
            $this->dispatcher,
            $this->logger,
            $this->markdown
        );
    }
}
