<?php

namespace Mitoop\Http;

class ResponseCode
{
    public static int $success = 0;

    public static int $error = 1;

    public static int $unauthenticated = -1;

    public static string $unauthenticatedMsg = 'Unauthenticated.';

    public static function setDefault(array $data): void
    {
        if (isset($data['success'])) {
            self::$success = (int) $data['success'];
        }

        if (isset($data['error'])) {
            self::$error = (int) $data['error'];
        }

        if (isset($data['unauthenticated'])) {
            self::$unauthenticated = (int) $data['unauthenticated'];
        }

        if (isset($data['unauthenticated_msg'])) {
            self::$unauthenticatedMsg = (string) $data['unauthenticated_msg'];
        }
    }
}
