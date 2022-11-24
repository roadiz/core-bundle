<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\ImageManager;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\SizeableInterface;
use RZ\Roadiz\Documents\Packages;
use RZ\Roadiz\Documents\SvgSizeResolver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DocumentSizeCommand extends Command
{
    protected SymfonyStyle $io;
    protected ManagerRegistry $managerRegistry;
    private Packages $packages;
    private ImageManager $imageManager;

    public function __construct(ManagerRegistry $managerRegistry, Packages $packages, ImageManager $imageManager)
    {
        parent::__construct();
        $this->managerRegistry = $managerRegistry;
        $this->packages = $packages;
        $this->imageManager = $imageManager;
    }

    protected function configure()
    {
        $this->setName('documents:size')
            ->setDescription('Fetch every document size (width and height) and write it in database.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $em = $this->managerRegistry->getManagerForClass(DocumentInterface::class);
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

        $q = $this->managerRegistry->getRepository(DocumentInterface::class)
            ->createQueryBuilder('d')
            ->getQuery();
        $iterableResult = $q->iterate();

        $this->io->progressStart($count);
        foreach ($iterableResult as $row) {
            $document = $row[0];
            if ($document instanceof SizeableInterface) {
                $this->updateDocumentSize($document);
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

    private function updateDocumentSize(DocumentInterface $document)
    {
        if (!($document instanceof SizeableInterface)) {
            return;
        }
        if ($document->isImage()) {
            $documentPath = $this->packages->getDocumentFilePath($document);
            try {
                $imageProcess = $this->imageManager->make($documentPath);
                $document->setImageWidth($imageProcess->width());
                $document->setImageHeight($imageProcess->height());
            } catch (NotReadableException $exception) {
                /*
                 * Do nothing
                 * just return 0 width and height
                 */
                $this->io->error($documentPath . ' is not a readable image.');
            }
        } elseif ($document->isSvg()) {
            try {
                $svgSizeResolver = new SvgSizeResolver($document, $this->packages);
                $document->setImageWidth($svgSizeResolver->getWidth());
                $document->setImageHeight($svgSizeResolver->getHeight());
            } catch (\RuntimeException $exception) {
                $this->io->error($exception->getMessage());
            }
        }
    }
}
