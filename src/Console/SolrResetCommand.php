<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\Core\SearchEngine\Indexer\NodesSourcesIndexer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command line utils for managing nodes from terminal.
 */
class SolrResetCommand extends SolrCommand
{
    protected function configure()
    {
        $this->setName('solr:reset')
            ->setDescription('Reset Solr search engine index');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->solr = $this->getHelper('solr')->getSolr();
        $this->io = new SymfonyStyle($input, $output);

        if (null !== $this->solr) {
            if (true === $this->getHelper('solr')->ready()) {
                $confirmation = new ConfirmationQuestion(
                    '<question>Are you sure to reset Solr index?</question>',
                    false
                );
                if ($this->io->askQuestion($confirmation)) {
                    /** @var NodesSourcesIndexer $nodesSourcesIndexer */
                    $nodesSourcesIndexer = $this->getHelper('kernel')->getKernel()->get(NodesSourcesIndexer::class);
                    $nodesSourcesIndexer->setIo($this->io);
                    $nodesSourcesIndexer->emptySolr();
                    $this->io->success('Solr index resetted.');
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
