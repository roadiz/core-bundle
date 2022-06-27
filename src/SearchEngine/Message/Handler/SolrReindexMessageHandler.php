<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine\Message\Handler;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\SearchEngine\Indexer\IndexerFactoryInterface;
use RZ\Roadiz\CoreBundle\SearchEngine\Message\SolrReindexMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class SolrReindexMessageHandler implements MessageHandlerInterface
{
    private LoggerInterface $logger;
    private IndexerFactoryInterface $indexerFactory;

    /**
     * @param IndexerFactoryInterface $indexerFactory
     * @param LoggerInterface $searchEngineLogger
     */
    public function __construct(IndexerFactoryInterface $indexerFactory, LoggerInterface $searchEngineLogger)
    {
        $this->logger = $searchEngineLogger;
        $this->indexerFactory = $indexerFactory;
    }

    public function __invoke(SolrReindexMessage $message)
    {
        try {
            if (!empty($message->getIdentifier())) {
                $this->indexerFactory->getIndexerFor($message->getClassname())->index($message->getIdentifier());
            }
        } catch (\LogicException $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
