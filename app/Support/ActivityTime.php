<?php

namespace App\Support;

use Illuminate\Http\Request;

class ActivityTime
{
    public static function normalize(Request $request, string $field): void
    {
        if (! $request->hasAny([$field.'_hour', $field.'_minute'])) {
            return;
        }
        $hour = $request->input($field.'_hour');
        $minute = $request->input($field.'_minute');
        if (($hour === null || $hour === '') && ($minute === null || $minute === '')) {
            $request->merge([$field => null]);

            return;
        }
        $valid = (is_string($hour) || is_int($hour)) && (is_string($minute) || is_int($minute))
            && preg_match('/^\d{1,2}$/', (string) $hour) && preg_match('/^\d{1,2}$/', (string) $minute);
        $request->merge([$field => $valid ? sprintf('%02d:%02d', (int) $hour, (int) $minute) : 'invalid']);
    }
}
