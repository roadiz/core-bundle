<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Command line utils for managing PHP-FPM Cache from terminal.
 */
class CacheFpmCommand extends Command
{
    protected string $cmsVersion;
    protected KernelInterface $kernel;

    /**
     * @param string $cmsVersion
     * @param KernelInterface $kernel
     */
    public function __construct(string $cmsVersion, KernelInterface $kernel)
    {
        parent::__construct();
        $this->cmsVersion = $cmsVersion;
        $this->kernel = $kernel;
    }

    protected function configure()
    {
        $this->setName('cache:clear-fpm')
            ->setDescription('Clear <info>PHP-FPM</info> cache through a cURL request.')
            ->addOption(
                'domain',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Customize <info>clear_cache.php</info> domain if it is not localhost.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $url = 'http://localhost/clear_cache.php';
        $scriptName = 'clear_cache.php';

        if ($input->getOption('domain') != '') {
            $url = 'http://'. $input->getOption('domain') . '/' . $scriptName;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidOptionException('Domain must be a valid domain name.');
        }

        try {
            $client = new Client();
            $client->get($url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'User-Agent' => 'Roadiz_CLI/'.$this->cmsVersion,
                ],
                'query' => [
                    'env' => $this->kernel->getEnvironment(),
                ],
                'allow_redirects' => true,
                'timeout' => 2
            ]);
            if ($io->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
                $io->note('Call web entry-point: ' . $url);
            }
            $io->success('PHP-FPM caches were cleared for '.$this->kernel->getEnvironment().' environement.');
        } catch (ConnectException $exception) {
            $io->warning('Cannot reach ' . $url . ' [' . $exception->getCode() . ']');
        } catch (ClientException $exception) {
            $io->warning('Cannot GET ' . $url . ' [' . $exception->getCode() . ']');
        }
        return 0;
    }
}
