<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\DependencyInjection;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class PackageMetadata
{
    private $namespace;

    /** @var array<int, string> */
    private $dirs;

    /**
     * @param array<int, string> $dirs
     */
    public function __construct(string $namespace, array $dirs)
    {
        $this->namespace = $namespace;
        $this->dirs = $dirs;
    }

    /**
     * @return iterable<string, string>
     */
    public function findPaths(?string $baseDir = null): iterable
    {
        $finder = new \AppendIterator();
        foreach ($this->dirs as $dir) {
            if (null !== $baseDir) {
                $dir .= '/'.$baseDir;
            }
            if (is_dir($dir)) {
                $finder->append(new \FilesystemIterator($dir, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS | \FilesystemIterator::CURRENT_AS_PATHNAME));
            }
        }

        return $finder;
    }

    /**
     * @return array<int, string>
     */
    public function getEventClasses(): array
    {
        $classes = [];

        foreach ($this->findPaths('Event') as $path) {
            if ('.php' === substr($path, -4) && is_file($path)) {
                $classes[] = $this->namespace.'Event\\'.basename($path, '.php');
            }
        }

        sort($classes);

        return $classes;
    }

    /**
     * @return array<int, string>
     */
    public function getDoctrineMappingFiles(): array
    {
        $files = [];

        foreach ($this->findPaths('Infrastructure/Doctrine/Resources/dist-mapping') as $path) {
            if ('.orm.xml' === substr($path, -8) && is_file($path)) {
                $files[] = $path;
            }
        }

        sort($files);

        return $files;
    }

    /**
     * @return array<string, string>
     */
    public function getDoctrineServicePrototypes(): array
    {
        $prototypes = [];

        foreach ($this->dirs as $dir) {
            if (is_dir(\dirname($resource = $dir.'/Infrastructure/Doctrine/*ObjectMappings.php'))) {
                $prototypes[$resource] = $this->namespace.'Infrastructure\\Doctrine\\';
            }
            if (is_dir(\dirname($resource = $dir.'/Infrastructure/Doctrine/Repository/*Repository.php'))) {
                $prototypes[$resource] = $this->namespace.'Infrastructure\\Doctrine\\Repository\\';
            }
        }

        return $prototypes;
    }

    /**
     * @return array<string, string>
     */
    public function getConsoleServicePrototypes(): array
    {
        $prototypes = [];

        foreach ($this->dirs as $dir) {
            if (is_dir(\dirname($resource = $dir.'/Infrastructure/Console/Command/*Command.php'))) {
                $prototypes[$resource] = $this->namespace.'Infrastructure\\Console\\Command\\';
            }
        }

        return $prototypes;
    }

    /**
     * @return array<string, string>
     */
    public function getMessageServicePrototypes(): array
    {
        $prototypes = [];

        foreach ($this->dirs as $dir) {
            if (is_dir(\dirname($resource = $dir.'/Command/Handler/*Handler.php'))) {
                $prototypes[$resource] = $this->namespace.'Command\\Handler\\';
            }
        }

        return $prototypes;
    }
}
