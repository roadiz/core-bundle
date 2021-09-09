<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\CoreBundle\Mailer\EmailManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MailerTestCommand extends Command
{
    protected EmailManager $emailManager;

    /**
     * @param EmailManager $emailManager
     */
    public function __construct(EmailManager $emailManager)
    {
        parent::__construct();
        $this->emailManager = $emailManager;
    }


    protected function configure()
    {
        $this->setName('mailer:send:test')
            ->addArgument('email', InputArgument::REQUIRED, 'Receiver email.')
            ->setDescription('Send a test email.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->emailManager
            ->setReceiver($input->getArgument('email'))
            ->setOrigin('origin@test.test')
            ->setSender('sender@roadiz.io')
            ->setSubject('Test email')
            ->setEmailPlainTextTemplate('@RoadizCore/email/base_email.txt.twig')
            ->setEmailTemplate('@RoadizCore/email/base_email.html.twig')
            ->setAssignation([
                'content' => 'This is a test email',
                'mailContact' => 'test@roadiz.io'
            ])
            ->send();
        (new SymfonyStyle($input, $output))->success('Email sent.');
        return 1;
    }
}
