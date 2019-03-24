<?php

declare(strict_types=1);

namespace MsgPhp\Domain\Projection;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class ProjectionDocument
{
    public const STATUS_UNKNOWN = 1;
    public const STATUS_SYNCHRONIZED = 2;
    public const STATUS_FAILED_TRANSFORMATION = 3;
    public const STATUS_FAILED_SAVING = 4;

    /**
     * @var int
     */
    public $status = self::STATUS_UNKNOWN;

    /**
     * @var object|null
     */
    public $source;

    /**
     * @var \Throwable|null
     */
    public $error;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var string|null
     */
    private $id;

    /**
     * @var array
     */
    private $body;

    public function __construct(string $type = null, string $id = null, array $body = [])
    {
        $this->type = $type;
        $this->id = $id;
        $this->body = $body;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function toProjection(): Projection
    {
        if (null === $this->type) {
            throw new \LogicException('Document type not set.');
        }

        if (!is_subclass_of($this->type, Projection::class)) {
            throw new \LogicException(sprintf('Document type must be a sub class of "%s", got "%s".', Projection::class, $this->type));
        }

        return $this->type::fromDocument($this->body);
    }
}
