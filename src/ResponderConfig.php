<?php

declare(strict_types=1);

namespace Mitoop\Http;

use Closure;

class ResponderConfig
{
    public const RESERVED_KEYS = [
        'success',
        'code',
        'message',
        'data',
        'meta',
        'errors',
    ];

    protected array $extra = [];

    protected int $success = 0;

    protected int $error = 1;

    protected int $deny = -1;

    protected int $successStatus = 200;

    protected int $errorStatus = 200;

    protected int $denyStatus = 200;

    protected string $successMessage = 'ok';

    protected string $errorMessage = 'error';

    protected string $denyMessage = 'unauthorized';

    /**
     * @param  array{
     *     success?: int,
     *     error?: int,
     *     deny?: int,
     *     message?: array{success?: string, error?: string, deny?: string},
     *     http_status?: array{success?: int, error?: int, deny?: int},
     *     extra?: array<string, mixed>
     * }  $data
     */
    public function apply(array $data): void
    {
        if (isset($data['success'])) {
            $this->success = (int) $data['success'];
        }

        if (isset($data['error'])) {
            $this->error = (int) $data['error'];
        }

        if (isset($data['deny'])) {
            $this->deny = (int) $data['deny'];
        }

        if (isset($data['message']) && is_array($data['message'])) {
            if (isset($data['message']['success'])) {
                $this->successMessage = (string) $data['message']['success'];
            }

            if (isset($data['message']['error'])) {
                $this->errorMessage = (string) $data['message']['error'];
            }

            if (isset($data['message']['deny'])) {
                $this->denyMessage = (string) $data['message']['deny'];
            }
        }

        if (isset($data['http_status']) && is_array($data['http_status'])) {
            if (isset($data['http_status']['success'])) {
                $this->successStatus = (int) $data['http_status']['success'];
            }

            if (isset($data['http_status']['error'])) {
                $this->errorStatus = (int) $data['http_status']['error'];
            }

            if (isset($data['http_status']['deny'])) {
                $this->denyStatus = (int) $data['http_status']['deny'];
            }
        }

        if (isset($data['extra']) && is_array($data['extra'])) {
            $this->extra = $data['extra'];
        }
    }

    /**
     * @param  array<array-key, mixed>  $values
     * @return array<string, mixed>
     */
    public function onlyExtra(array $values): array
    {
        $resolved = [];

        foreach ($values as $key => $value) {
            if (! is_string($key) || in_array($key, self::RESERVED_KEYS, true)) {
                continue;
            }

            $resolved[$key] = $value instanceof Closure ? $value() : $value;
        }

        return $resolved;
    }

    /**
     * @param  array<array-key, mixed>  $overrides
     * @return array<string, mixed>
     */
    public function mergedExtra(array $overrides = []): array
    {
        return $this->onlyExtra(array_merge($this->extra, $overrides));
    }

    /**
     * @return array<string, mixed>
     */
    public function extra(): array
    {
        return $this->mergedExtra();
    }

    public function success(): int
    {
        return $this->success;
    }

    public function error(): int
    {
        return $this->error;
    }

    public function deny(): int
    {
        return $this->deny;
    }

    public function successStatus(): int
    {
        return $this->successStatus;
    }

    public function errorStatus(): int
    {
        return $this->errorStatus;
    }

    public function denyStatus(): int
    {
        return $this->denyStatus;
    }

    public function successMessage(): string
    {
        return $this->successMessage;
    }

    public function errorMessage(): string
    {
        return $this->errorMessage;
    }

    public function denyMessage(): string
    {
        return $this->denyMessage;
    }
}
