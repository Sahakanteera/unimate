<?php

namespace App\Support;

class ActivityDate
{
    public static function toIso(mixed $value): mixed
    {
        if (is_string($value) && preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $value, $parts)) {
            return $parts[3].'-'.$parts[2].'-'.$parts[1];
        }

        return $value;
    }

    public static function display(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $parts)) {
            return $parts[3].'/'.$parts[2].'/'.$parts[1];
        }

        return $value;
    }
}
