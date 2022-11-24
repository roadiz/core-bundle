<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\FolderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class DocumentClearFolderCommand extends Command
{
    protected SymfonyStyle $io;
    private ManagerRegistry $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct();
        $this->managerRegistry = $managerRegistry;
    }

    protected function configure()
    {
        $this->setName('documents:clear-folder')
            ->addArgument('folderId', InputArgument::REQUIRED, 'Folder ID to delete documents from.')
            ->setDescription('Delete every document from folder. <info>Danger zone</info>')
        ;
    }

    protected function getDocumentQueryBuilder(ObjectManager $entityManager, FolderInterface $folder): QueryBuilder
    {
        $qb = $this->managerRegistry->getRepository(DocumentInterface::class)->createQueryBuilder('d');
        return $qb->innerJoin('d.folders', 'f')
            ->andWhere($qb->expr()->eq('f.id', ':folderId'))
            ->setParameter(':folderId', $folder);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $folderId = (int) $input->getArgument('folderId');
        if ($folderId <= 0) {
            throw new \InvalidArgumentException('Folder ID must be a valid ID');
        }
        $em = $this->managerRegistry->getManagerForClass(DocumentInterface::class);
        /** @var FolderInterface|null $folder */
        $folder = $em->find(FolderInterface::class, $folderId);
        if ($folder === null) {
            throw new \InvalidArgumentException(sprintf('Folder #%d does not exist.', $folderId));
        }

        $batchSize = 20;
        $i = 0;

        $count = (int) $this->getDocumentQueryBuilder($em, $folder)
            ->select('count(d)')
            ->getQuery()
            ->getSingleScalarResult();

        if ($count <= 0) {
            $this->io->warning('No documents were found in this folder.');
            return 0;
        }

        if (
            $this->io->askQuestion(new ConfirmationQuestion(
                sprintf('Are you sure to delete permanently %d documents?', $count),
                false
            ))
        ) {
            $results = $this->getDocumentQueryBuilder($em, $folder)
                ->select('d')
                ->getQuery()
                ->getResult();

            $this->io->progressStart($count);
            /** @var DocumentInterface $document */
            foreach ($results as $document) {
                $em->remove($document);
                if (($i % $batchSize) === 0) {
                    $em->flush(); // Executes all updates.
                }
                ++$i;
                $this->io->progressAdvance();
            }
            $em->flush();
            $this->io->progressFinish();
        }

        return 0;
    }
}
