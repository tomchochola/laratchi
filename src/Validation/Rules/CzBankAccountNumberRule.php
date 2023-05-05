<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Validation\Rules;

use Illuminate\Contracts\Validation\Rule as RuleContract;

class CzBankAccountNumberRule implements RuleContract
{
    public const PREFIX_WEIGHTS = [10, 5, 8, 4, 2, 1];

    public const BASE_WEIGHTS = [6, 3, 7, 9, 10, 5, 8, 4, 2, 1];

    /**
     * Bank codes.
     *
     * @var array<int, string>
     */
    public static array $bankCodes = [
        '0100',
        '0300',
        '0600',
        '0710',
        '0800',
        '2010',
        '2020',
        '2060',
        '2070',
        '2100',
        '2200',
        '2220',
        '2250',
        '2260',
        '2275',
        '2600',
        '2700',
        '3030',
        '3050',
        '3060',
        '3500',
        '4000',
        '4300',
        '5500',
        '5800',
        '6000',
        '6100',
        '6200',
        '6210',
        '6300',
        '6700',
        '6800',
        '7910',
        '7950',
        '7960',
        '7970',
        '7990',
        '8030',
        '8040',
        '8060',
        '8090',
        '8150',
        '8190',
        '8198',
        '8199',
        '8200',
        '8220',
        '8230',
        '8240',
        '8250',
        '8255',
        '8265',
        '8270',
        '8280',
        '8293',
        '8299',
        '8500',
    ];

    /**
     * Create a new rule instance.
     */
    public function __construct(protected bool $validateBankCode = true)
    {
    }

    /**
     * @inheritDoc
     */
    public function passes(mixed $attribute, mixed $value): bool
    {
        if (! \is_string($value)) {
            return false;
        }

        if (resolveApp()->runningUnitTests()) {
            return true;
        }

        if (\preg_match('/^(([0-9]{0,6})-)?([0-9]{2,10})\\/([0-9]{4})$/', $value, $parts) !== 1) {
            return false;
        }

        if (! $this->validatePrefix($parts)) {
            return false;
        }

        if (! $this->validateBase($parts)) {
            return false;
        }

        if (! $this->validateBankCode) {
            return true;
        }

        return \in_array($parts[4], static::$bankCodes, true);
    }

    /**
     * @inheritDoc
     *
     * @return string|array<int, string>
     */
    public function message(): string|array
    {
        return mustTransString('validation.regex');
    }

    /**
     * Validate prefix.
     *
     * @param array<int, string> $parts
     */
    protected function validatePrefix(array $parts): bool
    {
        if ($parts[2] === '') {
            return true;
        }

        $prefix = \str_pad($parts[2], 6, '0', \STR_PAD_LEFT);

        $sum = 0;

        for ($i = 0; $i < 6; ++$i) {
            $sum += (int) $prefix[$i] * static::PREFIX_WEIGHTS[$i];
        }

        if ($sum % 11 !== 0) {
            return false;
        }

        return true;
    }

    /**
     * Validate base.
     *
     * @param array<int, string> $parts
     */
    protected function validateBase(array $parts): bool
    {
        $base = \str_pad($parts[3], 10, '0', \STR_PAD_LEFT);

        $sum = 0;

        for ($i = 0; $i < 10; ++$i) {
            $sum += (int) $base[$i] * static::BASE_WEIGHTS[$i];
        }

        if ($sum % 11 !== 0) {
            return false;
        }

        return true;
    }
}
