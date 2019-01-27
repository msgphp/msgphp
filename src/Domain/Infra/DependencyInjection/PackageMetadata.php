<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\DependencyInjection;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class PackageMetadata
{
    private $namespace;
    private $dirs;

    /**
     * @param string[] $dirs
     */
    public function __construct(string $namespace, array $dirs)
    {
        if (!$dirs) {
            throw new \LogicException('Missing package directory.');
        }

        $this->namespace = $namespace;
        $this->dirs = $dirs;
    }

    /**
     * @return string[]
     */
    public function findPaths(string $baseDir = null): iterable
    {
        $finder = new \AppendIterator();
        foreach ($this->dirs as $dir) {
            if (null !== $baseDir) {
                $dir .= '/'.$baseDir;
            }
            $finder->append(new \FilesystemIterator($dir, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS | \FilesystemIterator::CURRENT_AS_PATHNAME));
        }

        return $finder;
    }

    /**
     * @return string[]
     */
    public function getEventClasses(): array
    {
        $classes = [];

        foreach ($this->findPaths('Event') as $path) {
            if ('Event.php' === substr($path, -9) && is_file($path)) {
                $classes[] = $this->namespace.'Event\\'.basename($path, '.php');
            }
        }

        sort($classes);

        return $classes;
    }

    /**
     * @return string[]
     */
    public function getDoctrineMappingFiles(): array
    {
        $files = [];

        foreach ($this->findPaths('Infra/Doctrine/Resources/dist-mapping') as $path) {
            if ('.orm.xml' === substr($path, -8) && is_file($path)) {
                $files[] = $path;
            }
        }

        sort($files);

        return $files;
    }

    /**
     * @return string[]
     */
    public function getDoctrineServicePrototypes(): array
    {
        $prototypes = [];

        foreach ($this->dirs as $dir) {
            if (is_dir(\dirname($resource = $dir.'/Infra/Doctrine/*ObjectMappings.php'))) {
                $prototypes[$resource] = $this->namespace.'Infra\\Doctrine\\';
            }
            if (is_dir(\dirname($resource = $dir.'/Infra/Doctrine/Repository/*Repository.php'))) {
                $prototypes[$resource] = $this->namespace.'Infra\\Doctrine\\Repository\\';
            }
        }

        return $prototypes;
    }

    /**
     * @return string[]
     */
    public function getConsoleServicePrototypes(): array
    {
        $prototypes = [];

        foreach ($this->dirs as $dir) {
            if (is_dir(\dirname($resource = $dir.'/Infra/Console/Command/*Command.php'))) {
                $prototypes[$resource] = $this->namespace.'Infra\\Console\\Command\\';
            }
        }

        return $prototypes;
    }

    /**
     * @return string[]
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
