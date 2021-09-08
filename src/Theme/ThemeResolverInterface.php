<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Theme;

use RZ\Roadiz\CoreBundle\Entity\Theme;

interface ThemeResolverInterface
{
    /**
     * @return Theme
     */
    public function getBackendTheme(): Theme;

    /**
     * @return class-string
     */
    public function getBackendClassName(): string;

    /**
     * @param string|null $host
     *
     * @return Theme|null
     */
    public function findTheme(string $host = null): ?Theme;


    /**
     * @param string $classname
     *
     * @return Theme|null
     */
    public function findThemeByClass(string $classname): ?Theme;

    /**
     * @return Theme[]
     */
    public function findAll(): array;

    /**
     * @param int $id
     *
     * @return Theme|null
     */
    public function findById($id): ?Theme;

    /**
     * @return Theme[]
     */
    public function getFrontendThemes(): array;
}
