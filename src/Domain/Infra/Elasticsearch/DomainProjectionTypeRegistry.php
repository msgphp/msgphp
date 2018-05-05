<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Elasticsearch;

use Elasticsearch\Client;
use MsgPhp\Domain\Projection\DomainProjectionTypeRegistryInterface;
use Psr\Log\LoggerInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainProjectionTypeRegistry implements DomainProjectionTypeRegistryInterface
{
    private const DEFAULT_PROPERTY_TYPE = 'text';

    private $client;
    private $index;
    private $mappings;
    private $settings;
    private $logger;
    private $types;

    public function __construct(Client $client, string $index, array $mappings, array $settings = [], LoggerInterface $logger = null)
    {
        $this->client = $client;
        $this->index = $index;
        $this->mappings = [];
        $this->settings = $settings;
        $this->logger = $logger;
    }

    /**
     * @return string[]
     */
    public function all(): array
    {
        return $this->types ?? ($this->types = array_keys($this->mappings));
    }

    public function initialize(): void
    {
        $indices = $this->client->indices();

        if ($indices->exists($params = ['index' => $this->index])) {
            return;
        }

        $indices->create($params + $this->getIndexParams());

        if (null !== $this->logger) {
            $this->logger->info('Initialized Elasticsearch index "{index}".', ['index' => $this->index]);
        }
    }

    public function destroy(): void
    {
        $indices = $this->client->indices();

        if (!$indices->exists($params = ['index' => $this->index])) {
            return;
        }

        $indices->delete($params);

        if (null !== $this->logger) {
            $this->logger->info('Destroyed Elasticsearch index "{index}".', ['index' => $this->index]);
        }
    }

    private function getIndexParams(): array
    {
        $params = [];

        if ($this->settings) {
            $params['body']['settings'] = $this->settings;
        }

        foreach ($this->provideMappings() as $type => $mapping) {
            foreach ($mapping as $property => $propertyMapping) {
                if (!is_array($propertyMapping)) {
                    $propertyMapping = ['type' => $propertyMapping];
                } elseif (!isset($propertyMapping['type'])) {
                    $propertyMapping['type'] = self::DEFAULT_PROPERTY_TYPE;
                }

                $params['body']['mappings'][$type]['properties'][$property] = $propertyMapping;
            }
        }

        return $params;
    }

    private function provideMappings(): iterable
    {
        foreach ($this->mappings as $type => $mapping) {
            if (is_string($mapping)) {
                if (!class_exists($mapping) || !is_subclass_of($mapping, DocumentMappingProviderInterface::class)) {
                    throw new \LogicException(sprintf('The class "%s" does not exists or is not a sub class of "%s".', $mapping, DocumentMappingProviderInterface::class));
                }

                yield from $mapping::provideDocumentMappings();
            }

            if (!is_array($mapping)) {
                throw new \LogicException(sprintf('Property mapping for type "%s" must be an array or string, got "%s".', $type, gettype($mapping)));
            }

            yield $type => $mapping;
        }
    }
}
