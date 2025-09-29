<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Cache\Clearer;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

final class AssetsFileClearer extends FileClearer
{
    public function clear(): bool
    {
        $fs = new Filesystem();
        $finder = new Finder();

        if ($fs->exists($this->getCacheDir())) {
            $finder->in($this->getCacheDir());
            $fs->remove($finder);
            $this->output .= 'Assets cache has been purged.';

            return true;
        }

        return false;
    }
}
