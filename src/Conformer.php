<?php

namespace NBerces\PHPUtils;

use DateTime;
use DateTimeZone;
use Exception;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class Conformer
 * @package NBerces\PHPUtils
 */
class Conformer
{
    public static function toBoolean($bool, array $options = [])
    {
        $resolver = new OptionsResolver();
        static::configureToBooleanOptions($resolver);
        $options = $resolver->resolve($options);

        if (is_int($bool)) {
            switch ($bool) {
                case 0:
                    $bool = false;
                    break;
                case 1:
                    $bool = true;
                    break;
            }
        } elseif (is_string($bool)) {
            if (0 == strcmp('0', $bool)
                || 0 == strcmp('false', $bool)
            ) {
                $bool = false;
            } elseif (0 == strcmp('1', $bool)
                || 0 == strcmp('true', $bool)
            ) {
                $bool = true;
            }
        }

        if (!is_bool($bool)) {
            return $options['default'];
        }

        return $bool;
    }

    public static function toDateTime($dttm, array $options = [])
    {
        $resolver = new OptionsResolver();
        static::configureToDateTimeOptions($resolver);
        $options = $resolver->resolve($options);

        if (is_string($dttm)) {
            try {
                $dttm = static::doMakeDateTime(
                    $dttm,
                    $options['defaultTZ']
                );
            } catch (Exception $e) {
            }
        } elseif (is_array($dttm)) {
            $day = 0;
            $hour = 0;
            $minute = 0;
            $month = 0;
            $year = 0;

            extract($dttm, EXTR_IF_EXISTS);

            static::toInteger($day, ['default' => 0, 'maxValue' => 31, 'minValue' => 1]);
            static::toInteger($hour, ['default' => 0, 'maxValue' => 23, 'minValue' => 0]);
            static::toInteger($minute, ['default' => 0, 'maxValue' => 59, 'minValue' => 0]);
            static::toInteger($month, ['default' => 0, 'maxValue' => 12, 'minValue' => 1]);
            static::toInteger($year, ['default' => 0]);

            if (0 < $day
                && 0 < $month
                && 0 < $year
            ) {
                $dttm = static::doMakeDateTime(
                    'now',
                    $options['defaultTZ']
                );

                try {
                    $dttm->setDate($year, $month, $day);
                    $dttm->setTime($hour, $minute);
                } catch (Exception $e) {
                }
            }
        }

        if (!($dttm instanceof DateTime)) {
            return $options['default'];
        }

        if (!is_null($options['format'])) {
            $dttm = $dttm->format($options['format']);
        }

        return $dttm;
    }

    public static function toEmailAddress($address, array $options = [])
    {
        $resolver = new OptionsResolver();
        static::configureToEmailAddressOptions($resolver);
        $options = $resolver->resolve($options);
        $address = static::toString(
            $address,
            [
                'default' => '',
                'maxLength' => 320,
                'trimWhitespace' => true,
            ]
        );

        if (false === filter_var($address, FILTER_VALIDATE_EMAIL)) {
            return $options['default'];
        }

        return $address;
    }

    public static function toFloat($num, array $options = [])
    {
        $resolver = new OptionsResolver();
        static::configureToFloatOptions($resolver);
        $options = $resolver->resolve($options);

        if (is_numeric($num)) {
            try {
                $num = (float)$num;
            } catch (Exception $e) {
            }
        }

        if (!is_float($num)) {
            return $options['default'];
        }

        if (!is_null($options['maxValue'])
            && $num > $options['maxValue']
        ) {
            $num = (float)$options['maxValue'];
        }

        if (!is_null($options['minValue'])
            && $num < $options['minValue']
        ) {
            $num = (float)$options['minValue'];
        }

        if (0 == $num
            && !$options['allowZero']
        ) {
            return $options['default'];
        }

        return $num;
    }

    public static function toInteger($num, array $options = [])
    {
        $resolver = new OptionsResolver();
        static::configureToIntegerOptions($resolver);
        $options = $resolver->resolve($options);

        if (is_numeric($num)) {
            try {
                $num = (int)$num;
            } catch (Exception $e) {
            }
        }

        if (!is_integer($num)) {
            return $options['default'];
        }

        if (!is_null($options['maxValue'])
            && $num > $options['maxValue']
        ) {
            $num = (int)$options['maxValue'];
        }

        if (!is_null($options['minValue'])
            && $num < $options['minValue']
        ) {
            $num = (int)$options['minValue'];
        }

        if (0 == $num
            && !$options['allowZero']
        ) {
            return $options['default'];
        }

        return $num;
    }

    public static function toPlainText($text, array $options = [])
    {
        $resolver = new OptionsResolver();
        static::configureToPlainTextOptions($resolver);
        $options = $resolver->resolve($options);
        $text = static::toString($text, ['default' => '']);

        if (!empty($text)) {
            $whitespace = '~~@~~';
            /**
             * Add some whitespace to the end of tags, otherwise
             * words run into each other once tags are stripped.
             */
            $text = str_replace('>', '>' . $whitespace, $text);
            $text = strip_tags($text);
            /**
             * Replace non-breaking space entities with regular spaces
             * before decoding, otherwise we end up with some hard-core
             * UTF-8 hex 0xc2 0xa0 mumbo-jumbo that isn't recognised as
             * white-space and ends up introducing a bunch of blank lines.
             */
            $text = str_replace('&nbsp;', ' ', $text);
            $text = htmlspecialchars_decode($text, ENT_QUOTES);
            $text = html_entity_decode($text, ENT_QUOTES);
            $text = str_replace(' ', $whitespace, $text);
            /**
             * Four or more white-spaces in a row means a double line-break
             * (new paragraph). Ensure the 'utf-8' modifier is used.
             */
            $text = preg_replace('/(\s*' . $whitespace . '\s*){4,}/mu', "\n\n", $text);
            /**
             * All other whitespace is reduced to a single white-space
             * character.
             */
            $text = preg_replace('/(\s*' . $whitespace . '\s*)+/mu', ' ', $text);
            /**
             * Reduce two or more empty lines to a single line-break.
             */
            $text = preg_replace('/^[[:space:]]{2,}/mu', "\n", $text);
        }

        return static::toString($text, $options);
    }

    public static function toString($str, array $options = [])
    {
        $resolver = new OptionsResolver();
        static::configureToStringOptions($resolver);
        $options = $resolver->resolve($options);

        if (is_numeric($str)
            ||
            (
                is_object($str)
                && method_exists($str, '__toString')
            )
        ) {
            $str = (string)$str;
        }

        if (!is_string($str)) {
            return $options['default'];
        }

        if (!empty($str)) {
            if ($options['compactWhitespace']) {
                $str = preg_replace('/\s+/', ' ', $str);
            }

            if ($options['trimWhitespace']) {
                $str = trim($str);
            }

            if (!is_null($options['maxLength'])
                && 0 < $options['maxLength']
            ) {
                $str = mb_substr($str, 0, $options['maxLength']);
            }
        }

        if (empty($str)
            && !$options['allowEmpty']
        ) {
            return $options['default'];
        }

        return $str;
    }

    protected static function configureCommonToOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'default' => null,
            ]
        );
    }

    protected static function configureToBooleanOptions(OptionsResolver $resolver)
    {
        static::configureCommonToOptions($resolver);
    }

    protected static function configureToDateTimeOptions(OptionsResolver $resolver)
    {
        static::configureCommonToOptions($resolver);

        $resolver->setDefaults(
            [
                'defaultTZ' => new DateTimeZone('UTC'),
                'format' => null,
            ]
        );

        $resolver->setAllowedTypes('defaultTZ', ['\DateTimeZone', 'string']);
        $resolver->setAllowedTypes('format', ['null', 'string']);

        $resolver->setNormalizer(
            'defaultTZ',
            function (Options $options, $value) {
                if (is_string($value)) {
                    $value = new DateTimeZone($value);
                }

                return $value;
            }
        );
    }

    protected static function configureToEmailAddressOptions(OptionsResolver $resolver)
    {
        static::configureCommonToOptions($resolver);
    }

    protected static function configureToFloatOptions(OptionsResolver $resolver)
    {
        static::configureCommonToOptions($resolver);

        $resolver->setDefaults(
            [
                'allowZero' => true,
                'maxValue' => null,
                'minValue' => null,
            ]
        );

        $resolver->setAllowedTypes('allowZero', 'boolean');
        $resolver->setAllowedTypes('maxValue', ['null', 'float', 'int']);
        $resolver->setAllowedTypes('minValue', ['null', 'float', 'int']);
    }

    protected static function configureToIntegerOptions(OptionsResolver $resolver)
    {
        static::configureToFloatOptions($resolver);
    }

    protected static function configureToPlainTextOptions(OptionsResolver $resolver)
    {
        static::configureToStringOptions($resolver);
    }

    protected static function configureToStringOptions(OptionsResolver $resolver)
    {
        static::configureCommonToOptions($resolver);

        $resolver->setDefaults(
            [
                'allowEmpty' => true,
                'compactWhitespace' => false,
                'maxLength' => null,
                'trimWhitespace' => false,
            ]
        );

        $resolver->setAllowedTypes('allowEmpty', 'boolean');
        $resolver->setAllowedTypes('compactWhitespace', 'boolean');
        $resolver->setAllowedTypes('maxLength', ['null', 'int']);
        $resolver->setAllowedTypes('trimWhitespace', 'boolean');
    }

    protected static function doMakeDateTime(
        $time = 'now',
        DateTimeZone $timezone = null
    ): DateTime {
        return new DateTime($time, $timezone);
    }
}
