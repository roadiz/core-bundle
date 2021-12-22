<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine\Message\Handler;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RZ\Roadiz\CoreBundle\SearchEngine\Indexer\IndexerFactoryInterface;
use RZ\Roadiz\CoreBundle\SearchEngine\Message\SolrReindexMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class SolrDeleteMessageHandler implements MessageHandlerInterface
{
    private LoggerInterface $logger;
    private IndexerFactoryInterface $indexerFactory;

    /**
     * @param IndexerFactoryInterface $indexerFactory
     * @param LoggerInterface|null $logger
     */
    public function __construct(IndexerFactoryInterface $indexerFactory, ?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
        $this->indexerFactory = $indexerFactory;
    }

    public function __invoke(SolrReindexMessage $message)
    {
        try {
            if (!empty($message->getIdentifier())) {
                $this->indexerFactory->getIndexerFor($message->getClassname())->delete($message->getIdentifier());
            }
        } catch (\LogicException $exception) {
            $this->logger->error($exception);
        }
    }
}
