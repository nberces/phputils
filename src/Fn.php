<?php

namespace NBerces\PHPUtils;

use Countable;
use DateTime;
use DateTimeZone;
use Exception;
use InvalidArgumentException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Traversable;

class Fn
{
    const CCTYPE_AMERICANEXPRESS = 'amex';
    const CCTYPE_DINERSCLUB = 'dc';
    const CCTYPE_JCB = 'jcb';
    const CCTYPE_MASTERCARD = 'mc';
    const CCTYPE_VISA = 'visa';

    public static function apply($var, $callback)
    {
        if (!is_array($var)
            && !($var instanceof Traversable)
        ) {
            $var = [$var];
        }

        foreach ($var as $idx => $param) {
            call_user_func($callback, $param, $idx);
        }
    }

    public static function applyToOne($var, $callback)
    {
        static::apply(static::singularise($var), $callback);
    }

    public static function conformToBoolean($bool, array $options = [])
    {
        $resolver = new OptionsResolver();
        static::configureConformToBooleanOptions($resolver);
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

    public static function conformToDateTime($dttm, array $options = [])
    {
        $resolver = new OptionsResolver();
        static::configureConformToDateTimeOptions($resolver);
        $options = $resolver->resolve($options);

        if (is_string($dttm)) {
            try {
                $dttm = static::doMakeDateTime(
                    $dttm,
                    $options['defaultTZ']
                );
            } catch (Exception $e) {
                ;
            }
        } elseif (is_array($dttm)) {
            $day = 0;
            $hour = 0;
            $minute = 0;
            $month = 0;
            $year = 0;

            extract($dttm, EXTR_IF_EXISTS);

            static::conformToInteger($day, ['default' => 0, 'maxValue' => 31, 'minValue' => 1]);
            static::conformToInteger($hour, ['default' => 0, 'maxValue' => 23, 'minValue' => 0]);
            static::conformToInteger($minute, ['default' => 0, 'maxValue' => 59, 'minValue' => 0]);
            static::conformToInteger($month, ['default' => 0, 'maxValue' => 12, 'minValue' => 1]);
            static::conformToInteger($year, ['default' => 0]);

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
                    ;
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

    public static function conformToEmailAddress($address)
    {
        $resolver = new OptionsResolver();
        static::configureConformToEmailAddressOptions($resolver);
        $options = $resolver->resolve($options);
        $address = static::conformToString(
            $address,
            [
                'default' => '',
                'maxLength' => 320,
                'trimWhitespace' => true
            ]
        );

        if (!static::isValidEmailAddress($address)) {
            return $options['default'];
        }

        return $address;
    }

    public static function conformToFloat($num, array $options = [])
    {
        $resolver = new OptionsResolver();
        static::configureConformToFloatOptions($resolver);
        $options = $resolver->resolve($options);

        if (is_numeric($num)) {
            try {
                $num = (float) $num;
            } catch (Exception $e) {
                ;
            }
        }

        if (!is_float($num)) {
            return $options['default'];
        }

        if (!is_null($options['maxValue'])
            && $num > $options['maxValue']
        ) {
            $num = (float) $options['maxValue'];
        }

        if (!is_null($options['minValue'])
            && $num < $options['minValue']
        ) {
            $num = (float) $options['minValue'];
        }

        if (0 == $num
            && !$options['allowZero']
        ) {
            return $options['default'];
        }

        return $num;
    }

    public static function conformToInteger($num, array $options = [])
    {
        $resolver = new OptionsResolver();
        static::configureConformToIntegerOptions($resolver);
        $options = $resolver->resolve($options);

        if (is_numeric($num)) {
            try {
                $num = (int) $num;
            } catch (Exception $e) {
                ;
            }
        }

        if (!is_integer($num)) {
            return $options['default'];
        }

        if (!is_null($options['maxValue'])
            && $num > $options['maxValue']
        ) {
            $num = (int) $options['maxValue'];
        }

        if (!is_null($options['minValue'])
            && $num < $options['minValue']
        ) {
            $num = (int) $options['minValue'];
        }

        if (0 == $num
            && !$options['allowZero']
        ) {
            return $options['default'];
        }

        return $num;
    }

    public static function conformToPlainText($text, array $options = [])
    {
        $resolver = new OptionsResolver();
        static::configureConformToPlainTextOptions($resolver);
        $options = $resolver->resolve($options);
        $text = static::conformToString($text, ['default' => '']);

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

        return static::conformToString($text, $options);
    }

    public static function conformToString($str, array $options = [])
    {
        $resolver = new OptionsResolver();
        static::configureConformToStringOptions($resolver);
        $options = $resolver->resolve($options);

        if (is_numeric($str)
            ||
            (
                is_object($str)
                && method_exists($str, '__toString')
            )
        ) {
            $str = (string) $str;
        }

        if (!is_string($str)) {
            $conformed[] = $options['default'];
            return;
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
                $str = substr($str, 0, $options['maxLength']);
            }
        }

        if (empty($str)
            && !$options['allowEmpty']
        ) {
            return $options['default'];
        }

        return $str;
    }

    public static function isValidCreditCardNumber($num, array $options = [])
    {
        $resolver = new OptionsResolver();
        static::configureIsValidCreditCardNumberOptions($resolver);
        $options = $resolver->resolve($options);

        $num = preg_replace('/[^0-9]/', '', static::conformToString($num, ['deafult' => '']));
        $isValid = true;

        switch ($options['CCType']) {
            case CCTYPE_AMERICANEXPRESS:
                $isValid = (bool) preg_match('/^3[47][0-9]{13}$/', $num);
                break;
            case CCTYPE_DINERSCLUB:
                $isValid = (bool) preg_match('/^3(0[0-5]|[68][0-9])[0-9]{11}$/', $num);
                break;
            case CCTYPE_JCB:
                $isValid = (bool) preg_match('/^(3[0-9]{4}|2131|1800)[0-9]{11}$/', $num);
                break;
            case CCTYPE_MASTERCARD:
                $isValid = (bool) preg_match('/^5[1-5][0-9]{14}$/', $num);
                break;
            case CCTYPE_VISA:
                $isValid = (bool) preg_match('/^4[0-9]{12}([0-9]{3})?$/', $num);
                break;
            default:
                throw new InvalidArgumentException();
        }

        if ($isValid
            && $options['performLuhnCheck']
        ) {
            $luhnCheckTotal = 0;

            for ($idx = 0; $idx < strlen($number); $idx++) {
                $digit = $number[$idx];
                if ($idx % 2 == (strlen($number) % 2)) {
                    $digit *= 2;
                    if ($digit > 9) {
                        $digit -= 9;
                    }
                }

                $luhnCheckTotal += $digit;
            }

            if ($luhnCheckTotal % 10 != 0) {
                $isValid = false;
            }
        }

        return $isValid;
    }

    public static function isValidEmailAddress($str)
    {
        if (1 == preg_match('/^[^@\s]+@([-a-z0-9]+\.)+[a-z]{2,}$/i', $str)) {
            return true;
        }

        return false;
    }

    /**
     * Helps determine the correct return-type for functions that perform
     * operations equally on both a single value or a collection of values.
     * Typically, a function that accepts a single-value argument will want
     * to return a single value; a function that accepts a collection of values
     * will want to return a collection.
     *
     * This function inspects $basedOn to determine whether to return
     * a single value or a collection of values, and then returns the appropriate
     * type using $subject. Below is a summary of expected behaviour:
     *
     * $basedOn (singular), $subject (singular) => $subject
     * $basedOn (singular), $subject (collection) => $subject[0]
     * $basedOn (collection), $subject (singular) => [$subject]
     * $basedOn (collection), $subject (collection) => $subject
     *
     * @param mixed $basedOn
     * @param mixed $subject
     *
     * @return mixed|[]
     */
    public static function matchForm($basedOn, $subject)
    {
        if (!is_array($basedOn)
            && !($basedOn instanceof Traversable)
        ) {
            /**
             * $basedOn determined to be of singular-form; return $subject
             * in singular form.
             */
            return static::singularise($subject);
        }

        if (!is_array($subject)
            && !($subject instanceof Traversable)
        ) {
            /**
             * $basedOn determined to be of collective-form, $subject determined
             * to be of singular-form; return $subject in collective form.
             */
            return [$subject];
        }

        /**
         * $basedOn determined to be of collective-form, $subject determined
         * to be of collective-form; return $subject as-is.
         */
        return $subject;
    }

    public static function singularise($var)
    {
        if (is_array($var)) {
            if (empty($var)) {
                $var = null;
            } else {
                $var = reset($var);
            }
        } elseif ($var instanceof Traversable
            && $var instanceof Countable
        ) {
            if (0 == count($var)) {
                $var = null;
            } else {
                foreach ($var as $singleVar) {
                    $var = $singleVar;
                    break;
                }
            }
        }

        return $var;
    }

    protected static function configureCommonConformOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'default' => null
            ]
        );
    }

    protected static function configureConformToBooleanOptions(OptionsResolver $resolver)
    {
        static::configureCommonConformOptions($resolver);
    }

    protected static function configureConformToDateTimeOptions(OptionsResolver $resolver)
    {
        static::configureCommonConformOptions($resolver);

        $resolver->setDefaults(
            [
                'defaultTZ' => new DateTimeZone('UTC'),
                'format' => null
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

    protected static function configureConformToEmailAddressOptions(OptionsResolver $resolver)
    {
        static::configureCommonConformOptions($resolver);
    }

    protected static function configureConformToFloatOptions(OptionsResolver $resolver)
    {
        static::configureCommonConformOptions($resolver);

        $resolver->setDefaults(
            [
                'allowZero' => true,
                'maxValue' => null,
                'minValue' => null
            ]
        );

        $resolver->setAllowedTypes('allowZero', 'boolean');
        $resolver->setAllowedTypes('maxValue', ['null', 'float', 'int']);
        $resolver->setAllowedTypes('minValue', ['null', 'float', 'int']);
    }

    protected static function configureConformToIntegerOptions(OptionsResolver $resolver)
    {
        static::configureConformToFloatOptions($resolver);
    }

    protected static function configureConformToPlainTextOptions(OptionsResolver $resolver)
    {
        static::configureConformToStringOptions($resolver);
    }

    protected static function configureConformToStringOptions(OptionsResolver $resolver)
    {
        static::configureCommonConformOptions($resolver);

        $resolver->setDefaults(
            [
                'allowEmpty' => true,
                'compactWhitespace' => false,
                'maxLength' => null,
                'trimWhitespace' => false
            ]
        );

        $resolver->setAllowedTypes('allowEmpty', 'boolean');
        $resolver->setAllowedTypes('compactWhitespace', 'boolean');
        $resolver->setAllowedTypes('maxLength', ['null', 'int']);
        $resolver->setAllowedTypes('trimWhitespace', 'boolean');
    }

    protected static function configureIsValidCreditCardNumberOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'CCType' => self::CCTYPE_VISA,
                'performLuhnCheck' => true
            ]
        );

        $resolver->setAllowedTypes('CCType', 'string');
        $resolver->setAllowedTypes('performLuhnCheck', 'boolean');
        $resolver->setAllowedValues(
            'CCType',
            [
                self::CCTYPE_AMERICANEXPRESS,
                self::CCTYPE_DINERSCLUB,
                self::CCTYPE_JCB,
                self::CCTYPE_MASTERCARD,
                self::CCTYPE_VISA
            ]
        );
        $resolver->setRequired(['CCType']);
    }

    protected static function doMakeDateTime(
        $time = 'now',
        DateTimeZone $timezone = null
    ) {
        return new DateTime($time, $timezone);
    }
}
