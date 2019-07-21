<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Elasticsearch;

use Elasticsearch\Client;
use MsgPhp\Domain\Projection\ProjectionTypeRegistry as BaseProjectionTypeRegistry;
use Psr\Log\LoggerInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ProjectionTypeRegistry implements BaseProjectionTypeRegistry
{
    private const DEFAULT_PROPERTY_TYPE = 'text';

    private $client;
    private $prefix;
    /** @var array<string, array> */
    private $mappings;
    private $settings;
    /** @var array<string, string> */
    private $lookup;
    private $logger;

    /**
     * @param array<string, array>  $mappings
     * @param array<string, string> $lookup
     */
    public function __construct(Client $client, string $prefix, array $mappings, array $settings = [], array $lookup = [], LoggerInterface $logger = null)
    {
        $this->client = $client;
        $this->prefix = $prefix;
        $this->mappings = $mappings;
        $this->settings = $settings;
        $this->lookup = $lookup;
        $this->logger = $logger;
    }

    public function initialize(string ...$type): void
    {
        $defaultSettings = $this->settings['*'] ?? [];
        $indices = $this->client->indices();

        foreach ($type ?: array_keys($this->mappings) as $type) {
            if (null === $mapping = $this->mappings[$type] ?? null) {
                throw new \LogicException('Unknown projection type "'.$type.'".');
            }

            $index = $this->prefix.$type;

            if ($indices->exists($params = ['index' => $index])) {
                continue;
            }

            $settings = $this->settings[$type] ?? [];
            $settings += $defaultSettings;

            if ($settings) {
                $params['body']['settings'] = $settings;
            }

            foreach ($mapping as $property => $propertyMapping) {
                if (!\is_array($propertyMapping)) {
                    $propertyMapping = ['type' => $propertyMapping];
                } elseif (!isset($propertyMapping['type'])) {
                    $propertyMapping['type'] = self::DEFAULT_PROPERTY_TYPE;
                }

                $params['body']['mappings']['properties'][$property] = $propertyMapping;
            }

            $indices->create($params);

            if (null !== $this->logger) {
                $this->logger->info('Initialized Elasticsearch index "'.$index.'".');
            }
        }
    }

    public function destroy(string ...$type): void
    {
        $indices = $this->client->indices();

        foreach ($type ?: array_keys($this->mappings) as $type) {
            if (!isset($this->mappings[$type])) {
                throw new \LogicException('Unknown projection type "'.$type.'".');
            }

            $index = $this->prefix.$type;

            if (!$indices->exists($params = ['index' => $index])) {
                continue;
            }

            $indices->delete($params);

            if (null !== $this->logger) {
                $this->logger->info('Destroyed Elasticsearch index "'.$index.'".');
            }
        }
    }

    public function lookup(string $name): string
    {
        if (isset($this->mappings[$name])) {
            return $name;
        }
        if (isset($this->lookup[$name])) {
            return $this->lookup[$name];
        }

        throw new \LogicException('Cannot lookup type for "'.$name.'".');
    }
}
