<?php

namespace NBerces\PHPUtils;

use Countable;
use Traversable;

class CompositeOperator
{
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

    /**
     * Helps determine the correct return-type for functions that perform
     * operations equally on both a single value or a collection of values.
     * Typically, a function that accepts a single-value argument will want
     * to return a single value; a function that accepts a collection of values
     * will want to return a collection.
     *
     * This function inspects $baseOn to determine whether to return
     * a single value or a collection of values, and then returns the appropriate
     * type using $subject. Below is a summary of expected behaviour:
     *
     * $baseOn (singular), $subject (singular) => $subject
     * $baseOn (singular), $subject (collection) => $subject[0]
     * $baseOn (collection), $subject (singular) => [$subject]
     * $baseOn (collection), $subject (collection) => $subject
     *
     * @param mixed $baseOn
     * @param mixed $subject
     *
     * @return mixed|[]
     */
    public static function matchForm($baseOn, $subject)
    {
        if (!is_array($baseOn)
            && !($baseOn instanceof Traversable)
        ) {
            /**
             * $baseOn determined to be of singular-form; return $subject
             * in singular form.
             */
            return static::singularise($subject);
        }

        if (!is_array($subject)
            && !($subject instanceof Traversable)
        ) {
            /**
             * $baseOn determined to be of collective-form, $subject determined
             * to be of singular-form; return $subject in collective form.
             */
            return [$subject];
        }

        /**
         * $baseOn determined to be of collective-form, $subject determined
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
}
