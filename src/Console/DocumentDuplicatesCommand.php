<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Document;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DocumentDuplicatesCommand extends Command
{
    protected ManagerRegistry $managerRegistry;
    protected SymfonyStyle $io;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct();
        $this->managerRegistry = $managerRegistry;
    }

    protected function configure()
    {
        $this->setName('documents:duplicates')
            ->setDescription('Find duplicated documents based on their file hash.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $em = $this->managerRegistry->getManagerForClass(Document::class);
        $documents = $em->getRepository(Document::class)->findDuplicates();
        $count = count($documents);
        $rows = [];

        if ($count <= 0) {
            $this->io->success('No duplicated documents were found.');
            return 0;
        }

        /** @var Document $document */
        foreach ($documents as $document) {
            $rows[] = [
                'ID' => $document->getId(),
                'Filename' => $document->getFilename(),
                'Hash' => $document->getFileHash(),
                'Algo' => $document->getFileHashAlgorithm(),
            ];
        }

        $this->io->table([
            'ID', 'Filename', 'Hash', 'Algo'
        ], $rows);

        return 0;
    }
}
