<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Doctrine;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class MappingCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var string
     */
    private $dirName;

    /**
     * @var MappingConfig
     */
    private $mappingConfig;

    public function __construct(string $dirName, MappingConfig $mappingConfig)
    {
        $this->dirName = $dirName;
        $this->mappingConfig = $mappingConfig;
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function warmUp($cacheDir): void
    {
        $filesystem = new Filesystem();
        $filesystem->mkdir($target = $cacheDir.'/'.$this->dirName);

        foreach ($this->mappingConfig->mappingFiles as $file) {
            $filename = basename($file);

            if ($this->mappingConfig->mappingDir && $filesystem->exists($this->mappingConfig->mappingDir.'/'.$filename)) {
                $filesystem->dumpFile($target.'/'.basename($file), $this->mappingConfig->interpolate((string) file_get_contents($this->mappingConfig->mappingDir.'/'.$filename)));
            } else {
                $filesystem->dumpFile($target.'/'.basename($file), $this->mappingConfig->interpolate((string) file_get_contents($file)));
            }
        }
    }
}
