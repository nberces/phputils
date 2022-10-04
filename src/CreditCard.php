<?php

namespace NBerces\PHPUtils;

use InvalidArgumentException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CreditCard
 * @package NBerces\PHPUtils
 */
class CreditCard
{
    public const TYPE_AMERICANEXPRESS = 'amex';
    public const TYPE_DINERSCLUB = 'dc';
    public const TYPE_JCB = 'jcb';
    public const TYPE_MASTERCARD = 'mc';
    public const TYPE_VISA = 'visa';

    public static function isValidNumber($num, array $options = [])
    {
        $resolver = new OptionsResolver();
        static::configureIsValidNumberOptions($resolver);
        $options = $resolver->resolve($options);

        $num = preg_replace('/[^0-9]/', '', Conformer::toString($num, ['deafult' => '']));
        $isValid = true;

        switch ($options['CCType']) {
            case self::TYPE_AMERICANEXPRESS:
                $isValid = (bool)preg_match('/^3[47][0-9]{13}$/', $num);
                break;
            case self::TYPE_DINERSCLUB:
                $isValid = (bool)preg_match('/^3(0[0-5]|[68][0-9])[0-9]{11}$/', $num);
                break;
            case self::TYPE_JCB:
                $isValid = (bool)preg_match('/^(3[0-9]{4}|2131|1800)[0-9]{11}$/', $num);
                break;
            case self::TYPE_MASTERCARD:
                $isValid = (bool)preg_match('/^5[1-5][0-9]{14}$/', $num);
                break;
            case self::TYPE_VISA:
                $isValid = (bool)preg_match('/^4[0-9]{12}([0-9]{3})?$/', $num);
                break;
            default:
                throw new InvalidArgumentException();
        }

        if ($isValid
            && $options['performLuhnCheck']
        ) {
            $isValid = self::checkLuhn($num);
        }

        return $isValid;
    }

    protected static function checkLuhn($num)
    {
        $luhnCheckTotal = 0;

        for ($idx = 0; $idx < strlen($num); $idx++) {
            $digit = $num[$idx];
            if ($idx % 2 == (strlen($num) % 2)) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $luhnCheckTotal += $digit;
        }

        if ($luhnCheckTotal % 10 != 0) {
            return false;
        }

        return true;
    }

    protected static function configureIsValidNumberOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'CCType' => self::TYPE_VISA,
                'performLuhnCheck' => true
            ]
        );

        $resolver->setAllowedTypes('CCType', 'string');
        $resolver->setAllowedTypes('performLuhnCheck', 'boolean');
        $resolver->setAllowedValues(
            'CCType',
            [
                self::TYPE_AMERICANEXPRESS,
                self::TYPE_DINERSCLUB,
                self::TYPE_JCB,
                self::TYPE_MASTERCARD,
                self::TYPE_VISA
            ]
        );
        $resolver->setRequired(['CCType']);
    }
}
