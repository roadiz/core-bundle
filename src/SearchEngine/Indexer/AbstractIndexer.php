<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine\Indexer;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RZ\Roadiz\CoreBundle\Exception\SolrServerNotAvailableException;
use RZ\Roadiz\CoreBundle\SearchEngine\ClientRegistry;
use RZ\Roadiz\CoreBundle\SearchEngine\SolariumFactoryInterface;
use Solarium\Client;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractIndexer implements Indexer
{
    private ClientRegistry $clientRegistry;
    protected SolariumFactoryInterface $solariumFactory;
    protected LoggerInterface $logger;
    protected ?SymfonyStyle $io = null;
    protected ManagerRegistry $managerRegistry;

    /**
     * @param ClientRegistry $clientRegistry
     * @param ManagerRegistry $managerRegistry
     * @param SolariumFactoryInterface $solariumFactory
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        ClientRegistry $clientRegistry,
        ManagerRegistry $managerRegistry,
        SolariumFactoryInterface $solariumFactory,
        ?LoggerInterface $logger = null
    ) {
        $this->solariumFactory = $solariumFactory;
        $this->clientRegistry = $clientRegistry;
        $this->logger = $logger ?? new NullLogger();
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @return Client
     */
    public function getSolr(): Client
    {
        $solr = $this->clientRegistry->getClient();
        if (null === $solr) {
            throw new SolrServerNotAvailableException();
        }
        return $solr;
    }

    /**
     * @param SymfonyStyle|null $io
     * @return AbstractIndexer
     */
    public function setIo(?SymfonyStyle $io)
    {
        $this->io = $io;
        return $this;
    }

    /**
     * Empty Solr index.
     *
     * @param string|null $documentType
     */
    public function emptySolr(?string $documentType = null): void
    {
        $update = $this->getSolr()->createUpdate();
        if (null !== $documentType) {
            $update->addDeleteQuery('document_type_s:' . trim($documentType));
        } else {
            // Delete ALL index
            $update->addDeleteQuery('*:*');
        }
        $update->addCommit();
        $this->getSolr()->update($update);
    }

    /**
     * Send an optimize and commit update query to Solr.
     */
    public function optimizeSolr(): void
    {
        $optimizeUpdate = $this->getSolr()->createUpdate();
        $optimizeUpdate->addOptimize(true, true, 5);
        $this->getSolr()->update($optimizeUpdate);

        $this->commitSolr();
    }

    public function commitSolr()
    {
        $finalCommitUpdate = $this->getSolr()->createUpdate();
        $finalCommitUpdate->addCommit(true, true, false);
        $this->getSolr()->update($finalCommitUpdate);
    }
}
