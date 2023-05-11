<?php

namespace NBerces\PHPUtils;

use DateInterval as BaseDateInterval;

class DateInterval extends BaseDateInterval
{
    public static function toSpecification(BaseDateInterval $interval): string
    {
        $dateElements = array_filter(
            [
                'Y' => $interval->y,
                'M' => $interval->m,
                'D' => $interval->d,
            ]
        );

        $timeElements = array_filter(
            [
                'H' => $interval->h,
                'M' => $interval->i,
                'S' => $interval->s,
            ]
        );

        $str = 'P';

        foreach ($dateElements as $period => $duration) {
            $str .= $duration . $period;
        }

        if (!empty($timeElements)) {
            $str .= 'T';

            foreach ($timeElements as $period => $duration) {
                $str .= $duration . $period;
            }
        }

        return $str;
    }

    public function __toString()
    {
        return self::toSpecification($this);
    }
}
