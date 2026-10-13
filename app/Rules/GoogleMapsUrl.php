<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class GoogleMapsUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $parts = parse_url($value);
        $host = strtolower($parts['host'] ?? '');
        $path = $parts['path'] ?? '';
        $valid = match ($host) {
            'google.com', 'www.google.com', 'google.co.th', 'www.google.co.th', 'goo.gl' => $path === '/maps' || str_starts_with($path, '/maps/'),
            'maps.google.com', 'maps.google.co.th' => true,
            'maps.app.goo.gl' => strlen(trim($path, '/')) > 0,
            default => false,
        };
        if (! $valid || ($parts['scheme'] ?? '') !== 'https'
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])) {
            $fail('กรุณาใช้ลิงก์สถานที่จาก Google Maps เช่น google.com/maps หรือ maps.app.goo.gl');
        }
    }
}
