<?php

namespace Mitoop\Http\Resources;

use Illuminate\Http\Request;
use Mitoop\Http\Config;

trait ResourceTrait
{
    public function withMessage($message): static
    {
        $this->with['message'] = $message;

        return $this;
    }

    public function jsonOptions(): int
    {
        return JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES;
    }

    public function with(Request $request): array
    {
        $data = [
            'code' => app(Config::class)->success(),
            'message' => 'ok',
        ];

        $extra = app(Config::class)->extra();
        if (! empty($extra)) {
            $data = array_merge($data, $extra);
        }

        return $data;
    }
}
