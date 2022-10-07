<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Utils\Asset\Packages;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DocumentFileHashCommand extends Command
{
    protected ManagerRegistry $managerRegistry;
    protected Packages $packages;
    protected SymfonyStyle $io;

    public function __construct(ManagerRegistry $managerRegistry, Packages $packages)
    {
        parent::__construct();
        $this->managerRegistry = $managerRegistry;
        $this->packages = $packages;
    }

    protected function configure()
    {
        $this->setName('documents:file:hash')
            ->setDescription('Compute every document file hash and store it.')
            ->addOption(
                'algorithm',
                'a',
                InputOption::VALUE_OPTIONAL,
                'Hash algorithm (see https://www.php.net/manual/fr/function.hash-algos.php) <info>Default: sha256</info>'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $batchSize = 20;
        $i = 0;
        $defaultAlgorithm = $input->getOption('algorithm') ?? 'sha256';
        if (!\in_array($defaultAlgorithm, \hash_algos())) {
            throw new \RuntimeException(sprintf(
                '“%s” algorithm is not available. Choose one from \hash_algos() method (%s)',
                $defaultAlgorithm,
                implode(', ', \hash_algos())
            ));
        }

        $em = $this->managerRegistry->getManagerForClass(Document::class);
        $documents = $em->getRepository(Document::class)->findAllWithoutFileHash();
        $count = count($documents);

        if ($count <= 0) {
            $this->io->success('All document files have hash.');
            return 0;
        }

        $this->io->progressStart($count);
        /** @var Document $document */
        foreach ($documents as $document) {
            $documentPath = $this->packages->getDocumentFilePath($document);
            $algorithm = $document->getFileHashAlgorithm() ?? $defaultAlgorithm;
            if (\file_exists($documentPath)) {
                if (false !== $fileHash = \hash_file($algorithm, $documentPath)) {
                    $document->setFileHash($fileHash);
                    $document->setFileHashAlgorithm($algorithm);
                }
            }

            if (($i % $batchSize) === 0) {
                $em->flush(); // Executes all updates.
            }
            ++$i;
            $this->io->progressAdvance();
        }
        $em->flush();
        $this->io->progressFinish();

        return 0;
    }
}
