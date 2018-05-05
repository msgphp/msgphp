<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infra\Elasticsearch;

use Elasticsearch\Client;
use MsgPhp\Domain\Projection\{DomainProjectionInterface, DomainProjectionTypeRegistryInterface};
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

        foreach ($mappings as $type => $propertyMapping) {
            if (!is_array($propertyMapping)) {
                throw new \LogicException(sprintf('Property mapping for type "%s" must be an array, got "%s".', $type, gettype($propertyMapping)));
            }

            foreach ($propertyMapping as $property => $mapping) {
                if (!is_array($mapping)) {
                    $mapping = ['type' => $mapping ?? self::DEFAULT_PROPERTY_TYPE];
                } elseif (!isset($info['type'])) {
                    $mapping['type'] = self::DEFAULT_PROPERTY_TYPE;
                }

                $this->mappings[$type]['properties'][$property] = $mapping;
            }
        }
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

        if ($this->settings) {
            $params['body']['settings'] = $this->settings;
        }
        if ($this->mappings) {
            $params['body']['mappings'] = $this->mappings;
        }

        $indices->create($params);

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
}
