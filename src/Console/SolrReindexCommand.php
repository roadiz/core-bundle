<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\CoreBundle\SearchEngine\ClientRegistry;
use RZ\Roadiz\CoreBundle\SearchEngine\Indexer\DocumentIndexer;
use RZ\Roadiz\CoreBundle\SearchEngine\Indexer\NodesSourcesIndexer;
use RZ\Roadiz\CoreBundle\SearchEngine\SolariumDocumentTranslation;
use RZ\Roadiz\CoreBundle\SearchEngine\SolariumNodeSource;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Command line utils for managing nodes from terminal.
 */
class SolrReindexCommand extends SolrCommand implements ThemeAwareCommandInterface
{
    protected ?QuestionHelper $questionHelper = null;
    protected NodesSourcesIndexer $nodesSourcesIndexer;
    protected DocumentIndexer $documentIndexer;

    /**
     * @param ClientRegistry $clientRegistry
     * @param NodesSourcesIndexer $nodesSourcesIndexer
     * @param DocumentIndexer $documentIndexer
     */
    public function __construct(
        ClientRegistry $clientRegistry,
        NodesSourcesIndexer $nodesSourcesIndexer,
        DocumentIndexer $documentIndexer
    ) {
        parent::__construct($clientRegistry);
        $this->nodesSourcesIndexer = $nodesSourcesIndexer;
        $this->documentIndexer = $documentIndexer;
    }

    protected function configure()
    {
        $this->setName('solr:reindex')
            ->setDescription('Reindex Solr search engine index')
            ->addOption('nodes', null, InputOption::VALUE_NONE, 'Reindex with only nodes.')
            ->addOption('documents', null, InputOption::VALUE_NONE, 'Reindex with only documents.')
            ->addOption('batch-count', null, InputOption::VALUE_REQUIRED, 'Split reindexing in batch (only for nodes).')
            ->addOption('batch-number', null, InputOption::VALUE_REQUIRED, 'Run a selected batch (only for nodes), <comment>first batch is 0</comment>.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $solr = $this->clientRegistry->getClient();
        $this->io = new SymfonyStyle($input, $output);

        if (null !== $solr) {
            if (true === $this->clientRegistry->isClientReady($solr)) {
                if ($this->io->confirm(
                    'Are you sure to reindex your Node and Document database?',
                    !$input->isInteractive()
                )) {
                    $stopwatch = new Stopwatch();
                    $stopwatch->start('global');
                    $this->nodesSourcesIndexer->setIo($this->io);
                    $this->documentIndexer->setIo($this->io);

                    if ($input->getOption('documents')) {
                        // Empty first
                        $this->documentIndexer->emptySolr(SolariumDocumentTranslation::DOCUMENT_TYPE);
                        $this->documentIndexer->reindexAll();

                        $stopwatch->stop('global');
                        $duration = $stopwatch->getEvent('global')->getDuration();
                        $this->io->success(sprintf('Document database has been re-indexed in %.2d ms.', $duration));
                    } elseif ($input->getOption('nodes')) {
                        $batchCount = (int) $input->getOption('batch-count') ?? 1;
                        $batchNumber = (int) $input->getOption('batch-number') ?? 0;
                        // Empty first ONLY if one batch or first batch.
                        if ($batchNumber === 0) {
                            $this->nodesSourcesIndexer->emptySolr(SolariumNodeSource::DOCUMENT_TYPE);
                        }
                        $this->nodesSourcesIndexer->reindexAll($batchCount, $batchNumber);

                        $stopwatch->stop('global');
                        $duration = $stopwatch->getEvent('global')->getDuration();
                        if ($batchCount > 1) {
                            $this->io->success(sprintf(
                                'Batch %d/%d of node database has been re-indexed in %.2d ms.',
                                $batchNumber + 1,
                                $batchCount,
                                $duration
                            ));
                        } else {
                            $this->io->success(sprintf('Node database has been re-indexed in %.2d ms.', $duration));
                        }
                    } else {
                        // Empty first
                        $this->nodesSourcesIndexer->emptySolr();
                        $this->documentIndexer->reindexAll();
                        $this->nodesSourcesIndexer->reindexAll();

                        $stopwatch->stop('global');
                        $duration = $stopwatch->getEvent('global')->getDuration();
                        $this->io->success(sprintf('Node and document database has been re-indexed in %.2d ms.', $duration));
                    }
                }
            } else {
                $this->io->error('Solr search engine server does not respond…');
                $this->io->note('See your config.yml file to correct your Solr connexion settings.');
                return 1;
            }
        } else {
            $this->displayBasicConfig();
        }
        return 0;
    }
}
