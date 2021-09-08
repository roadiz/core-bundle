<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\Core\SearchEngine\Indexer\DocumentIndexer;
use RZ\Roadiz\Core\SearchEngine\Indexer\NodesSourcesIndexer;
use RZ\Roadiz\Core\SearchEngine\SolariumDocumentTranslation;
use RZ\Roadiz\Core\SearchEngine\SolariumNodeSource;
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

    protected function configure()
    {
        $this->setName('solr:reindex')
            ->setDescription('Reindex Solr search engine index')
            ->addOption('nodes', null, InputOption::VALUE_NONE, 'Reindex with only nodes.')
            ->addOption('documents', null, InputOption::VALUE_NONE, 'Reindex with only documents.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->questionHelper = $this->getHelper('question');
        $this->solr = $this->getHelper('solr')->getSolr();
        $this->io = new SymfonyStyle($input, $output);

        if (null !== $this->solr) {
            if (true === $this->getHelper('solr')->ready()) {
                if ($this->io->confirm(
                    'Are you sure to reindex your Node and Document database?',
                    !$input->isInteractive()
                )) {
                    $stopwatch = new Stopwatch();
                    $stopwatch->start('global');

                    /** @var NodesSourcesIndexer $nodesSourcesIndexer */
                    $nodesSourcesIndexer = $this->getHelper('kernel')->getKernel()->get(NodesSourcesIndexer::class);
                    /** @var DocumentIndexer $documentIndexer */
                    $documentIndexer = $this->getHelper('kernel')->getKernel()->get(DocumentIndexer::class);

                    $nodesSourcesIndexer->setIo($this->io);
                    $documentIndexer->setIo($this->io);

                    if ($input->getOption('documents')) {
                        // Empty first
                        $documentIndexer->emptySolr(SolariumDocumentTranslation::DOCUMENT_TYPE);
                        $documentIndexer->reindexAll();

                        $stopwatch->stop('global');
                        $duration = $stopwatch->getEvent('global')->getDuration();
                        $this->io->success(sprintf('Document database has been re-indexed in %.2d ms.', $duration));
                    } elseif ($input->getOption('nodes')) {
                        // Empty first
                        $nodesSourcesIndexer->emptySolr(SolariumNodeSource::DOCUMENT_TYPE);
                        $nodesSourcesIndexer->reindexAll();

                        $stopwatch->stop('global');
                        $duration = $stopwatch->getEvent('global')->getDuration();
                        $this->io->success(sprintf('Node database has been re-indexed in %.2d ms.', $duration));
                    } else {
                        // Empty first
                        $nodesSourcesIndexer->emptySolr();
                        $documentIndexer->reindexAll();
                        $nodesSourcesIndexer->reindexAll();

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
