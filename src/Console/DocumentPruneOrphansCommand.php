<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Documents\Packages;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

final class DocumentPruneOrphansCommand extends Command
{
    protected ManagerRegistry $managerRegistry;
    private Packages $packages;
    protected SymfonyStyle $io;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param Packages $packages
     */
    public function __construct(ManagerRegistry $managerRegistry, Packages $packages)
    {
        parent::__construct();
        $this->managerRegistry = $managerRegistry;
        $this->packages = $packages;
    }

    protected function configure()
    {
        $this->setName('documents:prune:orphans')
            ->setDescription('Remove any document without existing file on filesystem, except embeds. <info>Danger zone</info>')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE)
        ;
    }

    /**
     * @return QueryBuilder
     */
    protected function getDocumentQueryBuilder(): QueryBuilder
    {
        return $this->managerRegistry->getRepository(Document::class)->createQueryBuilder('d');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->managerRegistry->getManagerForClass(Document::class);
        $filesystem = new Filesystem();
        $this->io = new SymfonyStyle($input, $output);
        $dryRun = $input->getOption('dry-run');
        if ($dryRun) {
            $this->io->note('Dry run');
        }
        $deleteCount = 0;
        $batchSize = 20;
        $i = 0;
        $count = (int) $this->getDocumentQueryBuilder()
            ->select('count(d)')
            ->getQuery()
            ->getSingleScalarResult();

        if ($count <= 0) {
            $this->io->warning('No document found');
            return 0;
        }

        $q = $this->getDocumentQueryBuilder()->getQuery();
        $iterableResult = $q->iterate();

        $this->io->progressStart($count);
        foreach ($iterableResult as $row) {
            /** @var Document $document */
            $document = $row[0];
            $this->checkDocumentFilesystem($document, $filesystem, $em, $deleteCount, $dryRun);
            if (($i % $batchSize) === 0 && !$dryRun) {
                $em->flush(); // Executes all updates.
                $em->clear(); // Detaches all objects from Doctrine!
            }
            ++$i;
            $this->io->progressAdvance();
        }
        if (!$dryRun) {
            $em->flush();
        }
        $this->io->progressFinish();
        $this->io->success(sprintf('%d documents were deleted.', $deleteCount));
        return 0;
    }

    /**
     * @param Document $document
     * @param Filesystem $filesystem
     * @param ObjectManager $entityManager
     * @param int $deleteCount
     * @param bool $dryRun
     */
    private function checkDocumentFilesystem(
        Document $document,
        Filesystem $filesystem,
        ObjectManager $entityManager,
        int &$deleteCount,
        bool $dryRun = false
    ): void {
        /*
         * Do not prune embed documents which may not have any file
         */
        if (!$document->isEmbed()) {
            $documentPath = $this->packages->getDocumentFilePath($document);
            if (!$filesystem->exists($documentPath)) {
                if ($this->io->isDebug() && !$this->io->isQuiet()) {
                    $this->io->writeln(sprintf('%s file does not exist, pruning document %s', $document->getRelativePath(), $document->getId()));
                }
                if (!$dryRun) {
                    $entityManager->remove($document);
                    $deleteCount++;
                }
            }
        }
    }
}
