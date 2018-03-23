<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Projection;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class DomainProjectionDocument
{
    private const DATA_TYPE_KEY = 'document_type';
    private const DATA_ID_KEY = 'document_id';

    public const STATUS_UNKNOWN = 1;
    public const STATUS_VALID = 2;
    public const STATUS_FAILED_TRANSFORMATION = 3;
    public const STATUS_FAILED_SAVING = 4;

    /** @var int */
    public $status = self::STATUS_UNKNOWN;

    /** @var array */
    public $data = [];

    /** @var object|null */
    public $source;

    /** @var \Exception|null $error */
    public $error;

    public static function create(string $type, string $id = null, array $body = []): self
    {
        $document = new self();
        $document->data[self::DATA_TYPE_KEY] = $type;
        $document->data[self::DATA_ID_KEY] = $id;
        $document->data += $body;

        return $document;
    }

    public function getType(): string
    {
        if (!isset($this->data[self::DATA_TYPE_KEY])) {
            throw new \LogicException('Document type not set.');
        }

        if (!is_subclass_of($type = $this->data[self::DATA_TYPE_KEY], DomainProjectionInterface::class)) {
            throw new \LogicException(sprintf('Document type must be a sub class of "%s", got "%s".', DomainProjectionInterface::class, $type));
        }

        return $type;
    }

    public function getId(): ?string
    {
        return $this->data[self::DATA_ID_KEY] ?? null;
    }

    public function getBody(): array
    {
        $data = $this->data;
        unset($data[self::DATA_TYPE_KEY], $data[self::DATA_ID_KEY]);

        return $data;
    }
}
