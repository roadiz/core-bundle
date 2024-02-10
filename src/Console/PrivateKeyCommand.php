<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Crypto\KeyChain\KeyChainInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class PrivateKeyCommand extends Command
{
    public function __construct(
        private readonly KeyChainInterface $keyChain,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('crypto:private-key:info')
            ->addArgument('key-name', InputArgument::REQUIRED)
            ->setDescription('Get a private or public key information')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $keyName  = $input->getArgument('key-name');
        $key = $this->keyChain->get($keyName);

        $io->table([
            'name',
            'type',
            'derivation',
            'usage',
            'base64',
        ], [[
            $keyName,
            $key->isAsymmetricKey() ? 'asymmetric' : 'symmetric',
            $key->isPublicKey() ? 'public' : 'private',
            $key->isSigningKey() ? 'signing' : 'encryption',
            base64_encode($key->getRawKeyMaterial())
        ]]);
        return 0;
    }
}
