<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\CoreBundle\Repository\NodeTypeRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class NodeTypesValidateFilesCommand extends Command
{
    public function __construct(
        private readonly NodeTypeRepositoryInterface $repository,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    #[\Override]
    protected function configure(): void
    {
        $this->setName('nodetypes:validate-files')
            ->setDescription('Import all node-type YAML files and validate them.')
            ->addArgument('file', InputArgument::OPTIONAL, 'Only file to validate')
        ;
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($onlyFile = $input->getArgument('file')) {
            $this->repository->findOneByName($onlyFile);
        } else {
            $this->repository->findAll();
        }

        $io = new SymfonyStyle($input, $output);
        $io->success('All node-type files are valid.');

        return Command::SUCCESS;
    }
}
