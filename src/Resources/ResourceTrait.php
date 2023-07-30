<?php

namespace Mitoop\Http\Resources;

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
}
