<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\ImageManager;
use RZ\Roadiz\Documents\AverageColorResolver;
use RZ\Roadiz\Documents\Models\AdvancedDocumentInterface;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Packages;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DocumentAverageColorCommand extends Command
{
    protected SymfonyStyle $io;
    private AverageColorResolver $colorResolver;
    private ManagerRegistry $managerRegistry;
    private Packages $packages;
    private ImageManager $imageManager;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param Packages $packages
     * @param ImageManager $imageManager
     */
    public function __construct(ManagerRegistry $managerRegistry, Packages $packages, ImageManager $imageManager)
    {
        parent::__construct();
        $this->managerRegistry = $managerRegistry;
        $this->packages = $packages;
        $this->imageManager = $imageManager;
    }

    protected function configure()
    {
        $this->setName('documents:color')
            ->setDescription('Fetch every document medium color and write it in database.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->colorResolver = new AverageColorResolver();

        $batchSize = 20;
        $i = 0;
        $manager = $this->managerRegistry->getManagerForClass(DocumentInterface::class);
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
            /** @var DocumentInterface $document */
            $document = $row[0];
            $this->updateDocumentColor($document);
            if (($i % $batchSize) === 0) {
                $manager->flush(); // Executes all updates.
                $manager->clear(); // Detaches all objects from Doctrine!
            }
            ++$i;
            $this->io->progressAdvance();
        }
        $manager->flush();
        $this->io->progressFinish();
        return 0;
    }

    private function updateDocumentColor(DocumentInterface $document)
    {
        if ($document->isImage() && $document instanceof AdvancedDocumentInterface) {
            $documentPath = $this->packages->getDocumentFilePath($document);
            try {
                $mediumColor = $this->colorResolver->getAverageColor($this->imageManager->make($documentPath));
                $document->setImageAverageColor($mediumColor);
            } catch (NotReadableException $exception) {
                /*
                 * Do nothing
                 * just return 0 width and height
                 */
                $this->io->error($documentPath . ' is not a readable image.');
            }
        }
    }
}
