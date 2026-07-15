<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    public static function formatIndonesianDate($date)
    {
        return Carbon::parse($date)
            ->setTimezone('Asia/Jakarta')
            ->translatedFormat('d F Y H:i');
        //'d F Y H:i'
        //'d F Y'
    }

    public static function formatIndonesianDateSimple($date)
    {
        return Carbon::parse($date)
            ->setTimezone('Asia/Jakarta')
            ->translatedFormat('d F Y');
    }
}
