<?php

namespace NBerces\PHPUtils;

use DateTime as BaseDateTime;

class DateTime extends BaseDateTime
{
    const FORMAT_HTML5_DATE = '~html5date';
    const FORMAT_HTML5_DATETIME = '~html5datetime';
    const FORMAT_MYSQL = '~mysql';
    const FORMAT_RELATIVE = '~';
    const FORMAT_TIMEAGO = '~';
    const FORMAT_SOLR = '~solr';

    public static function toHtml5DateFormat(BaseDateTime $date)
    {
        return $date->format('Y-m-d');
    }

    public static function toHtml5DateTimeFormat(BaseDateTime $date)
    {
        return $date->format('Y-m-d\TH:i');
    }

    public static function toMysqlDateTimeFormat(BaseDateTime $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public static function toSolrDateTimeFormat(BaseDateTime $date)
    {
        return $date->format('Y-m-d\TH:i:s.z\Z');
    }

    public static function toTimeAgoFormat(BaseDateTime $date)
    {
        $diff_seconds = time() - $date->format('U');
        $diff_weeks = floor($diff_seconds / 604800);
        $diff_seconds -= $diff_weeks * 604800;
        $diff_days = floor($diff_seconds / 86400);
        $diff_seconds -= $diff_days * 86400;
        $diff_hours = floor($diff_seconds / 3600);
        $diff_seconds -= $diff_hours * 3600;
        $diff_minutes = floor($diff_seconds / 60);
        $diff_seconds -= $diff_minutes * 60;

        if ($diff_days + $diff_weeks <= 0) {
            if ($diff_hours <= 0) {
                $str = $diff_minutes . ' minutes ago';
            } else {
                $str = $diff_hours . ' hours, ' . $diff_minutes . ' minutes ago';
            }
        } elseif ($diff_days == 1
            && $diff_weeks <= 0
        ) {
            $str = 'Yesterday, ' . $date->format('g:i A');
        } elseif ($diff_weeks < 1) {
            $str = $date->format('l, g:i A');
        } elseif ($diff_weeks == 1) {
            $str = 'Last week, ' . $date->format('l');
        } elseif ($diff_weeks < 4) {
            $str = $diff_weeks . ' weeks ago, ' . $date->format('l');
        } else {
            $str = $date->format('F jS, Y');
        }

        return $str;
    }

    public function format($format)
    {
        switch ($format) {
            case self::FORMAT_HTML5_DATE:
                return self::toHtml5DateFormat($this);
            case self::FORMAT_HTML5_DATETIME:
                return self::toHtml5DateTimeFormat($this);
            case self::FORMAT_MYSQL:
                return self::toMysqlDateTimeFormat($this);
            case self::FORMAT_RELATIVE:
            case self::FORMAT_TIMEAGO:
                return self::toTimeAgoFormat($this);
            case self::FORMAT_SOLR:
                return self::toSolrDateTimeFormat($this);
        }

        return parent::format($format);
    }
}
