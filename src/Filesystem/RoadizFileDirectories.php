<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Filesystem;

use RZ\Roadiz\Core\Models\FileAwareInterface;

final class RoadizFileDirectories implements FileAwareInterface
{
    private string $projectDir;

    /**
     * @param string $projectDir
     */
    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    public function getPublicFilesPath(): string
    {
        return $this->projectDir . '/public' . $this->getPublicFilesBasePath();
    }

    public function getPublicFilesBasePath(): string
    {
        return '/files';
    }

    public function getPrivateFilesPath(): string
    {
        return $this->projectDir . '/var' . $this->getPrivateFilesBasePath();
    }

    public function getPrivateFilesBasePath(): string
    {
        return '/files/private';
    }

    public function getFontsFilesPath(): string
    {
        return $this->projectDir . '/var' . $this->getFontsFilesBasePath();
    }

    public function getFontsFilesBasePath(): string
    {
        return '/files/fonts';
    }

    public function getPublicCachePath(): string
    {
        return $this->projectDir . '/public' . $this->getPublicCacheBasePath();
    }

    public function getPublicCacheBasePath(): string
    {
        return '/assets';
    }
}
