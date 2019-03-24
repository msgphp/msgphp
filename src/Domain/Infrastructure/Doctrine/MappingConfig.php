<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Doctrine;

/**
 * @author Pascal Wacker <hello@pascalwacker.ch>
 */
final class MappingConfig
{
    private const DEFAULT_KEY_MAX_LENGTH = 191;

    /**
     * @var string[]
     */
    public $mappingFiles;

    /**
     * @var string|null
     */
    public $mappingDir;

    /**
     * @var int
     */
    public $keyMaxLength = self::DEFAULT_KEY_MAX_LENGTH;

    /**
     * @param string[] $mappingFiles
     */
    public function __construct(array $mappingFiles, array $mappingConfig = [])
    {
        $this->mappingFiles = $mappingFiles;

        if (isset($mappingConfig['mapping_dir'])) {
            $this->mappingDir = (string) $mappingConfig['mapping_dir'];
        }
        if (isset($mappingConfig['key_max_length'])) {
            $this->keyMaxLength = (int) $mappingConfig['key_max_length'];
        }
    }

    /**
     * Replaces config values in template and returns it.
     */
    public function interpolate(string $contents): string
    {
        return str_replace('%msgphp.doctrine.mapping_config.key_max_length%', (string) ($this->keyMaxLength ?? self::DEFAULT_KEY_MAX_LENGTH), $contents);
    }
}
