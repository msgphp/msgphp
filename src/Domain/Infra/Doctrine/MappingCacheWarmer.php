<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Doctrine;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
final class MappingCacheWarmer implements CacheWarmerInterface
{
    private $dirName;
    private $mappingFiles;

    public function __construct(string $dirName, array $mappingFiles)
    {
        $this->dirName = $dirName;
        $this->mappingFiles = $mappingFiles;
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function warmUp($cacheDir): void
    {
        $filesystem = new Filesystem();
        $filesystem->mkdir($target = $cacheDir.'/'.$this->dirName);

        foreach ($this->mappingFiles as $file) {
            $filesystem->copy($file, $target.'/'.basename($file));
        }
    }
}
