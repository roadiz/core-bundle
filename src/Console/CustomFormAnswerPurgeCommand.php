<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\CustomForm;
use RZ\Roadiz\CoreBundle\Entity\CustomFormAnswer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class CustomFormAnswerPurgeCommand extends Command
{
    private ManagerRegistry $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry, string $name = null)
    {
        parent::__construct($name);
        $this->managerRegistry = $managerRegistry;
    }

    protected function configure()
    {
        $this->setName('custom-form-answer:prune')
            ->setDescription('Prune all custom-form answers older than custom-form retention time policy.')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $customForms = $this->managerRegistry
            ->getRepository(CustomForm::class)
            ->findAllWithRetentionTime();

        foreach ($customForms as $customForm) {
            $interval = $customForm->getRetentionTimeInterval();
            if (null !== $interval = $customForm->getRetentionTimeInterval()) {
                $purgeBefore = (new \DateTime())->sub($interval);
                $customFormAnswers = $this->managerRegistry
                    ->getRepository(CustomFormAnswer::class)
                    ->findByCustomFormSubmittedBefore($customForm, $purgeBefore);
                $count = count($customFormAnswers);
                $io->info(\sprintf(
                    'Purge %d answer(s) from “%s” before %s',
                    $count,
                    $customForm->getName(),
                    $purgeBefore->format('Y-m-d H:i')
                ));

                if (
                    $count > 0 &&
                    !$input->getOption('dry-run') &&
                    (!$input->isInteractive() || $io->confirm(\sprintf(
                        'Are you sure you want to delete %d answer(s) from “%s” before %s',
                        count($customFormAnswers),
                        $customForm->getName(),
                        $purgeBefore->format('Y-m-d H:i')
                    ), false))
                ) {
                    $this->managerRegistry
                        ->getRepository(CustomFormAnswer::class)
                        ->deleteByCustomFormSubmittedBefore($customForm, $purgeBefore);
                }
            }
        }

        return 0;
    }
}
