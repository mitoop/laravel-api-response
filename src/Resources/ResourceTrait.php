<?php

namespace Mitoop\Http\Resources;

use Illuminate\Http\Request;
use Mitoop\Http\ResponseCode;

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

    public function with(Request $request)
    {
        return [
            'code' => ResponseCode::$success,
            'message' => 'success',
        ];
    }
}
