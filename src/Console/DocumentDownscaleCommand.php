<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use Intervention\Image\Exception\NotReadableException;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Event\Cache\CachePurgeAssetsRequestEvent;
use RZ\Roadiz\Utils\Document\DownscaleImageManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Command line utils for process document downscale.
 */
class DocumentDownscaleCommand extends Command
{
    private ?int $maxPixelSize;
    private DownscaleImageManager $downscaler;
    private ManagerRegistry $managerRegistry;
    private EventDispatcherInterface $dispatcher;

    /**
     * @param int|null $maxPixelSize
     * @param DownscaleImageManager $downscaler
     * @param ManagerRegistry $managerRegistry
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        ?int $maxPixelSize,
        DownscaleImageManager $downscaler,
        ManagerRegistry $managerRegistry,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct();
        $this->maxPixelSize = $maxPixelSize;
        $this->downscaler = $downscaler;
        $this->managerRegistry = $managerRegistry;
        $this->dispatcher = $dispatcher;
    }

    protected function configure()
    {
        $this->setName('documents:downscale')
            ->setDescription('Downscale every document according to max pixel size defined in configuration.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $entityManager = $this->managerRegistry->getManagerForClass(Document::class);

        if (null !== $this->maxPixelSize && $this->maxPixelSize > 0) {
            $confirmation = new ConfirmationQuestion(
                '<question>Are you sure to downscale all your image documents to ' . $this->maxPixelSize . 'px?</question>',
                false
            );
            if ($io->askQuestion(
                $confirmation
            )) {
                /** @var Document[] $documents */
                $documents = $entityManager
                    ->getRepository(Document::class)
                    ->findBy([
                        'mimeType' => [
                            'image/png',
                            'image/jpeg',
                            'image/gif',
                            'image/tiff',
                        ],
                        'raw' => false,
                    ]);
                $io->progressStart(count($documents));

                foreach ($documents as $document) {
                    try {
                        $this->downscaler->processDocumentFromExistingRaw($document);
                    } catch (NotReadableException $exception) {
                        $io->error($exception->getMessage() . ' - ' . (string) $document);
                    }
                    $io->progressAdvance();
                }

                $io->progressFinish();
                $io->success('Every documents have been downscaled, a raw version has been kept.');

                $event = new CachePurgeAssetsRequestEvent();
                $this->dispatcher->dispatch($event);
            }
            return 0;
        } else {
            $io->warning('Your configuration is not set for downscaling documents.');
            $io->note('Add <info>assetsProcessing.maxPixelSize</info> parameter in your <info>config.yml</info> file.');
            return 1;
        }
    }
}
