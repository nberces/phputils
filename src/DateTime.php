<?php

namespace PHPUtils;

class DateTime extends \DateTime
{
    const FORMAT_HTML5_DATE = '~html5date';
    const FORMAT_HTML5_DATETIME = '~html5datetime';
    const FORMAT_MYSQL = '~mysql';
    const FORMAT_RELATIVE = '~';
    const FORMAT_SOLR = '~solr';

    public function format($format)
    {
        if (0 === strcmp($format, self::FORMAT_HTML5_DATE)) {
            return parent::format('Y-m-d');
        }

        if (0 === strcmp($format, self::FORMAT_HTML5_DATETIME)) {
            return parent::format('Y-m-d\TH:i');
        }

        if (0 === strcmp($format, self::FORMAT_MYSQL)) {
            return parent::format('Y-m-d H:i:s');
        }

        if (0 === strcmp($format, self::FORMAT_RELATIVE)) {
            $diff_seconds = time() - parent::format('U');
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
                    $contextualDttmString =
                        $diff_minutes . ' minutes ago';
                } else {
                    $contextualDttmString =
                        $diff_hours . ' hours, '
                        . $diff_minutes . ' minutes ago';
                }
            } elseif ($diff_days == 1
                && $diff_weeks <= 0
            ) {
                $contextualDttmString =
                    'Yesterday, '
                    . parent::format('g:i A');

            } elseif ($diff_weeks < 1) {
                $contextualDttmString =
                    parent::format('l, g:i A');
            } elseif ($diff_weeks == 1) {
                $contextualDttmString =
                    'Last week, '
                    . parent::format('l');
            } elseif ($diff_weeks < 4) {
                $contextualDttmString =
                    $diff_weeks . ' weeks ago, '
                    . parent::format('l');
            } else {
                $contextualDttmString =
                    parent::format('F jS, Y');
            }

            return $contextualDttmString;
        }

        if (0 === strcmp($format, self::FORMAT_SOLR)) {
            return parent::format('Y-m-d\TH:i:s.z\Z');
        }

        return parent::format($format);
    }
}
