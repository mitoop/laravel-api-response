<?php

declare(strict_types=1);

namespace Mitoop\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Mitoop\Http\EnvelopeFactory;

/**
 * @mixin JsonResource
 */
trait ResourceTrait
{
    protected ?int $responseStatus = null;

    public function withMessage(string $message): static
    {
        $this->with['message'] = $message;

        return $this;
    }

    public function withStatus(int $status): static
    {
        $this->responseStatus = $status;

        return $this;
    }

    public function jsonOptions(): int
    {
        return EnvelopeFactory::JSON_OPTIONS;
    }

    public function toResponse($request): JsonResponse
    {
        return app(EnvelopeFactory::class)->generateResource(
            $this->resolve($request),
            $this->resource,
            array_merge($this->with($request), $this->additional),
            $this->responseStatus,
        );
    }
}
