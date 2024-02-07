<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command line utils for managing users from terminal.
 */
final class UsersDeleteCommand extends UsersCommand
{
    protected function configure(): void
    {
        $this->setName('users:delete')
            ->setDescription('Delete a user permanently')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'Username'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('username');

        if ($name) {
            /** @var User|null $user */
            $user = $this->managerRegistry
                ->getRepository(User::class)
                ->findOneBy(['username' => $name]);

            if (null !== $user) {
                $confirmation = new ConfirmationQuestion(
                    '<question>Do you really want to delete user “' . $user->getUsername() . '”?</question>',
                    false
                );
                if (
                    !$input->isInteractive() || $io->askQuestion(
                        $confirmation
                    )
                ) {
                    $this->managerRegistry->getManagerForClass(User::class)->remove($user);
                    $this->managerRegistry->getManagerForClass(User::class)->flush();
                    $io->success('User “' . $name . '” deleted.');
                } else {
                    $io->warning('User “' . $name . '” was not deleted.');
                }
            } else {
                throw new \InvalidArgumentException('User “' . $name . '” does not exist.');
            }
        }
        return 0;
    }
}
