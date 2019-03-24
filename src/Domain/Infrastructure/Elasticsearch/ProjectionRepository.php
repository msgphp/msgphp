<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Infrastructure\Elasticsearch;

use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use MsgPhp\Domain\GenericPaginatedDomainCollection;
use MsgPhp\Domain\PaginatedDomainCollectionInterface;
use MsgPhp\Domain\Projection\ProjectionDocument;
use MsgPhp\Domain\Projection\ProjectionRepositoryInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ProjectionRepository implements ProjectionRepositoryInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $index;

    public function __construct(Client $client, string $index)
    {
        $this->client = $client;
        $this->index = $index;
    }

    /**
     * @return PaginatedDomainCollectionInterface|ProjectionDocument[]
     */
    public function findAll(string $type, int $offset = 0, int $limit = 0): PaginatedDomainCollectionInterface
    {
        $params = [
            'index' => $this->index,
            'type' => $type,
            'body' => [
                'from' => $offset,
                'query' => ['match_all' => new \stdClass()],
            ],
        ];

        if ($limit) {
            $params['body']['size'] = $limit;
        }

        $result = $this->client->search($params);
        $documents = $result['hits']['hits'] ?? [];
        $count = \count($documents);
        $totalCount = $result['hits']['total'] ?? $count;

        return new GenericPaginatedDomainCollection((function () use ($documents): iterable {
            foreach ($documents as $document) {
                yield $this->createDocument($document);
            }
        })(), (float) $offset, (float) $limit, (float) $count, (float) $totalCount);
    }

    public function find(string $type, string $id): ?ProjectionDocument
    {
        try {
            $document = $this->client->get([
                'index' => $this->index,
                'type' => $type,
                'id' => $id,
            ]);
        } catch (Missing404Exception $e) {
            return null;
        }

        return $this->createDocument($document);
    }

    public function clear(string $type): void
    {
        $this->client->deleteByQuery([
            'index' => $this->index,
            'type' => $type,
            'body' => [
                'query' => ['match_all' => new \stdClass()],
            ],
        ]);
    }

    public function save(ProjectionDocument $document): void
    {
        $params = ['index' => $this->index, 'type' => $document->getType(), 'body' => $document->getBody()];
        if (null !== $id = $document->getId()) {
            $params['id'] = $id;
        }

        $this->client->index($params);
    }

    public function delete(string $type, string $id): void
    {
        $this->client->delete([
            'index' => $this->index,
            'type' => $type,
            'id' => $id,
        ]);
    }

    private function createDocument(array $data): ProjectionDocument
    {
        return new ProjectionDocument($data['_type'] ?? null, $data['_id'] ?? null, $data['_source'] ?? []);
    }
}
