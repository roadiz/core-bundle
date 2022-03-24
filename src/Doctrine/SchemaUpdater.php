<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Cache\Clearer\OPCacheClearer;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

final class SchemaUpdater
{
    private KernelInterface $kernel;
    private LoggerInterface $logger;
    private OPCacheClearer $opCacheClearer;

    /**
     * @param KernelInterface $kernel
     * @param OPCacheClearer $opCacheClearer
     * @param LoggerInterface $logger
     */
    public function __construct(
        KernelInterface $kernel,
        OPCacheClearer $opCacheClearer,
        LoggerInterface $logger
    ) {
        $this->kernel = $kernel;
        $this->logger = $logger;
        $this->opCacheClearer = $opCacheClearer;
    }

    public function clearMetadata(): void
    {
        $this->opCacheClearer->clear();
        $input = new ArrayInput([
            'command' => 'cache:pool:clear',
            'pools' => [
                'cache.app',
                'cache.system',
                'cache.validator',
                'cache.serializer',
                'cache.annotations',
                'cache.property_info',
                'cache.messenger.restart_workers_signal',
                'doctrine.result_cache_pool',
                'doctrine.system_cache_pool',
                'cache.doctrine.orm.default.metadata',
            ],
            '--no-interaction' => true,
        ]);
        $output = new BufferedOutput();
        $exitCode = $this->createApplication()->run($input, $output);
        $content = $output->fetch();
        if ($exitCode === 0) {
            $this->logger->info('Cleared cache pool.');
        } else {
            throw new \RuntimeException('Cannot clear cache pool: ' . $content);
        }

        $input = new ArrayInput([
            'command' => 'doctrine:cache:clear-result',
            '--no-interaction' => true,
            '--flush' => true,
        ]);
        $output = new BufferedOutput();
        $exitCode = $this->createApplication()->run($input, $output);
        $content = $output->fetch();
        if ($exitCode === 0) {
            $this->logger->info('Cleared all result cache for an entity manager.');
        } else {
            throw new \RuntimeException('Cannot clear result cache for an entity manager: ' . $content);
        }

        $input = new ArrayInput([
            'command' => 'doctrine:cache:clear-metadata',
            '--no-interaction' => true,
            '--flush' => true,
        ]);
        $output = new BufferedOutput();
        $exitCode = $this->createApplication()->run($input, $output);
        $content = $output->fetch();
        if ($exitCode === 0) {
            $this->logger->info('Cleared all Metadata cache entries.');
        } else {
            throw new \RuntimeException('Cannot clear all Metadata cache entries: ' . $content);
        }
    }

    protected function createApplication(): Application
    {
        /*
         * Very important, when using standard-edition,
         * Kernel class is AppKernel or DevAppKernel.
         */
        $application = new Application($this->kernel);
        $application->setAutoExit(false);
        return $application;
    }

    /**
     * Update database schema using doctrine migration.
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateSchema(): void
    {
        $this->clearMetadata();

        /*
         * Execute pending application migrations
         */
        $input = new ArrayInput([
            'command' => 'doctrine:migrations:migrate',
            '--no-interaction' => true,
            '--allow-no-migration' => true
        ]);
        $output = new BufferedOutput();
        $exitCode = $this->createApplication()->run($input, $output);
        $content = $output->fetch();
        if ($exitCode === 0) {
            $this->logger->info('Executed pending migrations.', ['migration' => $content]);
        } else {
            throw new \RuntimeException('Migrations failed: ' . $content);
        }
    }

    /**
     * @throws \Exception
     */
    public function updateNodeTypesSchema(): void
    {
        /*
         * Execute pending application migrations
         */
        $this->updateSchema();

        /*
         * Update schema with new node-types
         * without creating any migration
         */
        $input = new ArrayInput([
            'command' => 'doctrine:schema:update',
            '--dump-sql' => true,
            '--force' => true,
        ]);
        $output = new BufferedOutput();
        $exitCode = $this->createApplication()->run($input, $output);
        $content = $output->fetch();

        if ($exitCode === 0) {
            $this->logger->info('DB schema has been updated.', ['sql' => $content]);
        } else {
            throw new \RuntimeException('DB schema update failed: ' . $content);
        }
    }
}
