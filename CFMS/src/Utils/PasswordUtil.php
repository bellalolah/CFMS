<?php

namespace Cfms\Utils;

class PasswordUtil
{
    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT,['cost' => 12]);
    }

    public static function verify(string $password, string $hashedPassword): bool
    {
        return password_verify($password, $hashedPassword);
    }
}
