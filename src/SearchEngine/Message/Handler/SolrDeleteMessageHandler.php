<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine\Message\Handler;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Exception\SolrServerNotAvailableException;
use RZ\Roadiz\CoreBundle\SearchEngine\Indexer\IndexerFactoryInterface;
use RZ\Roadiz\CoreBundle\SearchEngine\Message\SolrDeleteMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class SolrDeleteMessageHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly IndexerFactoryInterface $indexerFactory,
        private readonly LoggerInterface $searchEngineLogger
    ) {
    }

    public function __invoke(SolrDeleteMessage $message): void
    {
        try {
            if (!empty($message->getIdentifier())) {
                // Cannot typehint with class-string: breaks Symfony Serializer 5.4
                // @phpstan-ignore-next-line
                $this->indexerFactory->getIndexerFor($message->getClassname())->delete($message->getIdentifier());
            }
        } catch (SolrServerNotAvailableException $exception) {
            return;
        } catch (\LogicException $exception) {
            $this->searchEngineLogger->error($exception->getMessage());
        }
    }
}
