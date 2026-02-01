<?php

namespace App\Helpers;

use Carbon\Carbon;

class SessionDurationHelper
{
    public static function calculate(
        Carbon $start,
        Carbon $end
    ): int {
        return $start->diffInSeconds($end);
    }
}
