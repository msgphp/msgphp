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
    private $client;
    private $index;
    private $mappings;
    private $settings;
    private $logger;
    private $types;

    public function __construct(Client $client, string $index, array $mappings, array $settings = [], LoggerInterface $logger = null)
    {
        foreach ($mappings as $type => $mapping) {
            if (!isset($mapping['properties']) || !is_array($mapping['properties'])) {
                continue;
            }

            foreach ($mapping['properties'] as $property => $info) {
                if (!is_array($info)) {
                    $info = ['type' => $info ?? 'text'];
                }

                $mappings[$type]['properties'][$property] = $info + ['type' => 'text'];
            }
        }

        $this->client = $client;
        $this->index = $index;
        $this->mappings = $mappings;
        $this->settings = $settings;
        $this->logger = $logger;
    }

    /**
     * @return string[]
     */
    public function all(): array
    {
        if (null === $this->types) {
            $this->types = [];
            foreach (array_keys($this->mappings) as $type) {
                if (is_subclass_of($type, DomainProjectionInterface::class)) {
                    $this->types[] = $type;
                }
            }
        }

        return $this->types;
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
