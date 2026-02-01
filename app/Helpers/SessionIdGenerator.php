<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class SessionIdGenerator
{
    public static function generate(int $length = 6): string
    {
        return strtolower(Str::random($length));
    }
}
