<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Theme;

use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Filesystem\Filesystem;

final class ThemeInfo
{
    private string $name;
    private string $themeName;
    private ?string $classname = null;
    private Filesystem $filesystem;
    private string $projectDir;
    private ?string $themePath = null;
    private static array $protectedThemeNames = ['DefaultTheme', 'Debug', 'BaseTheme', 'Install', 'Rozier'];

    /**
     * @param string $name Short theme name or FQN classname
     * @param string $projectDir
     */
    public function __construct(string $name, string $projectDir)
    {
        $this->filesystem = new Filesystem();
        $this->projectDir = $projectDir;

        if (false !== strpos($name, '\\')) {
            /*
             * If name is a FQN classname
             */
            $this->classname = $this->validateClassname($name);
            $this->name = $this->extractNameFromClassname($this->classname);
            $this->themeName = $this->getThemeNameFromName();
        } else {
            $this->name = $this->validateName($name);
            $this->themeName = $this->getThemeNameFromName();
        }
    }

    public function isProtected(): bool
    {
        return in_array($this->getThemeName(), self::$protectedThemeNames) && $this->getThemeName() !== 'Rozier';
    }

    /**
     * @param string $themeName
     *
     * @return class-string
     */
    protected function guessClassnameFromThemeName(string $themeName): string
    {
        switch ($themeName) {
            case 'RozierApp':
            case 'RozierTheme':
            case 'Rozier':
                return '\\Themes\\Rozier\\RozierApp';
            case 'Install':
            case 'InstallTheme':
            case 'InstallApp':
                return '\\Themes\\Install\InstallApp';
            case 'Debug':
                throw new \InvalidArgumentException('Debug is not a real theme.');
            case 'Default':
            case 'DefaultTheme':
                return '\\Themes\\DefaultTheme\\DefaultThemeApp';
            default:
                return '\\Themes\\'.$themeName.'\\'.$themeName. 'App';
        }
    }

    /**
     * @param string $classname
     *
     * @return string
     */
    protected function extractNameFromClassname(string $classname): string
    {
        $shortName = $this->getThemeReflectionClass($classname)->getShortName();

        return preg_replace('#(?:Theme)?(?:App)?$#', '', $shortName);
    }

    /**
     * @param string $classname
     * @return string
     */
    protected function validateClassname(string $classname): string
    {
        if (null !== $reflection = $this->getThemeReflectionClass($classname)) {
            return call_user_func([$reflection->getName(), 'getThemeMainClass']);
        }
        throw new RuntimeException('Theme class ' . $classname . ' does not exist.');
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function validateName(string $name): string
    {
        if (1 !== preg_match('#^[A-Z][a-zA-Z]+$#', $name)) {
            throw new LogicException('Theme name must only contain alphabetical characters and begin with uppercase letter.');
        }

        $name = trim(preg_replace('#(?:Theme)?(?:App)?$#', '', $name));
        if (!empty($name)) {
            return $name;
        }
        throw new LogicException('Theme name is not valid.');
    }

    /**
     * @return bool
     */
    public function exists(): bool
    {
        if ($this->isProtected()) {
            return true;
        }
        if ($this->filesystem->exists($this->getThemePath()) ||
            $this->filesystem->exists($this->projectDir . '/vendor/roadiz/' . $this->getThemeName()) ||
            $this->filesystem->exists($this->projectDir . '/vendor/roadiz/roadiz/themes/' . $this->getThemeName())
        ) {
            return true;
        }

        return false;
    }

    protected function getProtectedThemePath(): string
    {
        if ($this->filesystem->exists($this->projectDir . '/vendor/roadiz/roadiz/themes/' . $this->getThemeName())) {
            return $this->projectDir . '/vendor/roadiz/roadiz/themes/' . $this->getThemeName();
        } elseif ($this->filesystem->exists($this->projectDir . '/themes/' . $this->getThemeName())) {
            return $this->projectDir . '/themes/' . $this->getThemeName();
        }
        throw new \InvalidArgumentException($this->getThemeName() . ' does not exist in project and vendor.');
    }

    /**
     * Get real theme path from its name.
     *
     * Attention: theme could be located in vendor folder (/vendor/roadiz/roadiz)
     *
     * @return string Theme absolute path.
     */
    public function getThemePath(): string
    {
        if (null === $this->themePath) {
            if ($this->isProtected()) {
                $this->themePath = $this->getProtectedThemePath();
            } elseif ($this->isValid()) {
                $this->themePath = call_user_func([$this->getClassname(), 'getThemeFolder']);
            } else {
                $this->themePath = $this->projectDir . '/themes/' . $this->getThemeName();
            }
        }
        return $this->themePath;
    }

    /**
     * @param string|null $className
     *
     * @return null|ReflectionClass
     */
    public function getThemeReflectionClass(string $className = null): ?ReflectionClass
    {
        try {
            if (null === $className) {
                $className = $this->getClassname();
            }
            $reflection = new ReflectionClass($className);
            if ($reflection->isSubclassOf(AppController::class)) {
                return $reflection;
            }
        } catch (ReflectionException $Exception) {
            return null;
        }

        return null;
    }

    /**
     * @return string
     */
    protected function getThemeNameFromName(): string
    {
        if (in_array($this->name, self::$protectedThemeNames)) {
            return $this->name;
        }

        return $this->name . 'Theme';
    }

    /**
     * @return string Theme name WITHOUT suffix
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string Theme name WITH suffix
     */
    public function getThemeName(): string
    {
        return $this->themeName;
    }

    /**
     * @return string Theme class FQN
     */
    public function getClassname(): string
    {
        if (null === $this->classname) {
            $this->classname = $this->guessClassnameFromThemeName($this->getThemeName());
        }
        return $this->classname;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        try {
            $className = $this->getClassname();
        } catch (\InvalidArgumentException $exception) {
            return false;
        }

        try {
            $reflection = new ReflectionClass($className);
            if ($reflection->isSubclassOf(AppController::class)) {
                return true;
            }
        } catch (ReflectionException $Exception) {
            return false;
        }
        return false;
    }
}
