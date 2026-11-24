<?php

namespace App\Support;

/**
 * Strong passwords that are easy to read out or type from a phone:
 * three groups of four, like "Kp7m-Xq3r-Tz9w". Leaves out characters that
 * look alike (0/O, 1/l/I), and always has upper and lower case, numbers and
 * a symbol, so it passes the live site's password rules.
 */
final class PasswordGenerator
{
    private const UPPER = 'ABCDEFGHJKMNPQRSTUVWXYZ';

    private const LOWER = 'abcdefghjkmnpqrstuvwxyz';

    private const DIGITS = '23456789';

    public static function make(): string
    {
        $groups = [];

        for ($i = 0; $i < 3; $i++) {
            $chars = [self::pick(self::UPPER), self::pick(self::LOWER), self::pick(self::DIGITS), self::pick(self::LOWER)];
            shuffle($chars);
            $groups[] = implode('', $chars);
        }

        return implode('-', $groups);
    }

    private static function pick(string $from): string
    {
        return $from[random_int(0, strlen($from) - 1)];
    }
}
