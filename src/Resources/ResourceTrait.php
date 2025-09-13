<?php

namespace Mitoop\Http\Resources;

use Illuminate\Http\Request;
use Mitoop\Http\JsonResponderDefault;
use stdClass;

trait ResourceTrait
{
    public function withMessage($message): static
    {
        $this->with['message'] = $message;

        return $this;
    }

    public function jsonOptions(): int
    {
        return JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
    }

    public function with(Request $request): array
    {
        $data = [
            'code' => app(JsonResponderDefault::class)->success(),
            'message' => 'ok',
            'meta' => new stdClass,
        ];

        return array_merge($data, app(JsonResponderDefault::class)->extra() ?: []);
    }
}
