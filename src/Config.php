<?php

namespace Mitoop\Http;

class Config
{
    protected array $extra = [];

    protected int $success = 0;

    protected int $error = 1;

    protected int $reject = -1;

    /**
     * @param array{
     *     success?: int,
     *     error?: int,
     *     reject?: int,
     *     extra?: array
     * } $data
     */
    public function setDefaults(array $data): void
    {
        if (isset($data['success'])) {
            $this->success = (int) $data['success'];
        }

        if (isset($data['error'])) {
            $this->error = (int) $data['error'];
        }

        if (isset($data['reject'])) {
            $this->reject = (int) $data['reject'];
        }

        if (isset($data['extra']) && is_array($data['extra'])) {
            $this->extra = $data['extra'];
        }
    }

    public function extra(): array
    {
        return $this->extra;
    }

    public function success(): int
    {
        return $this->success;
    }

    public function error(): int
    {
        return $this->error;
    }

    public function reject(): int
    {
        return $this->reject;
    }
}
