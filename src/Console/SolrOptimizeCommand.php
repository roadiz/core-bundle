<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\CoreBundle\SearchEngine\ClientRegistry;
use RZ\Roadiz\CoreBundle\SearchEngine\Indexer\NodesSourcesIndexer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command line utils for managing nodes from terminal.
 */
class SolrOptimizeCommand extends SolrCommand
{
    protected NodesSourcesIndexer $nodesSourcesIndexer;

    /**
     * @param ClientRegistry $clientRegistry
     * @param NodesSourcesIndexer $nodesSourcesIndexer
     */
    public function __construct(ClientRegistry $clientRegistry, NodesSourcesIndexer $nodesSourcesIndexer)
    {
        parent::__construct($clientRegistry);
        $this->nodesSourcesIndexer = $nodesSourcesIndexer;
    }

    protected function configure()
    {
        $this->setName('solr:optimize')
            ->setDescription('Optimize Solr search engine index');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $solr = $this->clientRegistry->getClient();
        $this->io = new SymfonyStyle($input, $output);

        if (null !== $solr) {
            if (true === $this->clientRegistry->isClientReady($solr)) {
                $this->nodesSourcesIndexer->setIo($this->io);
                $this->nodesSourcesIndexer->optimizeSolr();
                $this->io->success('<info>Solr core has been optimized.</info>');
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
