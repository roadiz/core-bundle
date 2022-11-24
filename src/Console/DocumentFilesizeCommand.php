<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Documents\Models\AdvancedDocumentInterface;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Packages;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;

class DocumentFilesizeCommand extends Command
{
    protected Packages $packages;
    protected ManagerRegistry $managerRegistry;
    protected SymfonyStyle $io;

    /**
     * @param Packages $packages
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(Packages $packages, ManagerRegistry $managerRegistry)
    {
        parent::__construct();
        $this->packages = $packages;
        $this->managerRegistry = $managerRegistry;
    }

    protected function configure()
    {
        $this->setName('documents:file:size')
            ->setDescription('Fetch every document file size (in bytes) and write it in database.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->managerRegistry->getManagerForClass(DocumentInterface::class);
        $this->io = new SymfonyStyle($input, $output);

        $batchSize = 20;
        $i = 0;
        $count = (int) $this->managerRegistry->getRepository(DocumentInterface::class)
            ->createQueryBuilder('d')
            ->select('count(d)')
            ->getQuery()
            ->getSingleScalarResult();
        if ($count < 1) {
            $this->io->success('No document found');
            return 0;
        }
        $q = $em->getRepository(DocumentInterface::class)
            ->createQueryBuilder('d')
            ->getQuery();
        $iterableResult = $q->iterate();

        $this->io->progressStart($count);
        foreach ($iterableResult as $row) {
            $document = $row[0];
            if ($document instanceof AdvancedDocumentInterface) {
                $this->updateDocumentFilesize($document);
                if (($i % $batchSize) === 0) {
                    $em->flush(); // Executes all updates.
                    $em->clear(); // Detaches all objects from Doctrine!
                }
                ++$i;
                $this->io->progressAdvance();
            }
        }
        $em->flush();
        $this->io->progressFinish();
        return 0;
    }

    private function updateDocumentFilesize(AdvancedDocumentInterface $document)
    {
        if (null !== $document->getRelativePath()) {
            $documentPath = $this->packages->getDocumentFilePath($document);
            try {
                $file = new File($documentPath);
                $document->setFilesize($file->getSize());
            } catch (FileNotFoundException $exception) {
                /*
                 * Do nothing
                 * just return 0 width and height
                 */
                $this->io->error($documentPath . ' file not found.');
            }
        }
    }
}
