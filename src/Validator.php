<?php

namespace Clair\Ach;

use Clair\Ach\Definitions\FieldTypes;
use Clair\Ach\Exceptions\AchValidationException;
use Clair\Ach\Support\Utils;

class Validator
{
    public const ADDENDA_TYPE_CODES = ['02', '05', '98', '99'];

    public const ADDENDA_TRANSACTION_CODES = ['22', '23', '24', '27', '28', '29', '32', '33', '34', '37', '38', '39'];

    public const SERVICE_CLASS_CODES = ['200', '220', '225'];

    protected static string $numericRegex = '/^[0-9]+$/';

    protected static string $alphaRegex = '/^[a-zA-Z]+$/';

    protected static string $alphanumericRegex = '/^[0-9a-zA-Z!"#$%&\'()*+,-.\/:;<>=?@\[\]\\^_`{}|~ ]+$/';

    protected Entry $entry;

    /**
     * Validate required fields.
     *
     * @param array $fields
     * @return bool
     * @throws AchValidationException
     */
    public function validateRequiredFields(array $fields): bool
    {
        $fields = array_filter($fields, fn ($field) => array_key_exists('required', $field) && $field['required'] === true);

        foreach ($fields as $field) {
            if ($field['value'] === null || strlen($field['value']) === 0) {
                throw new AchValidationException('Missing required field: '.$field['name']);
            }
        }

        return true;
    }

    /**
     * Validate value lengths.
     *
     * @param array $fields
     * @return bool
     * @throws AchValidationException
     */
    public function validateLengths(array $fields): bool
    {
        $fields = array_filter($fields, fn ($field) => array_key_exists('width', $field));

        foreach ($fields as $field) {
            if (strlen($field['value']) > $field['width']) {
                $length = strlen($field['value']);
                throw new AchValidationException("Invalid length: {$field['name']}'s length is {$length} but should be no greater than {$field['width']}");
            }
        }

        return true;
    }

    /**
     * Validate data types.
     *
     * @param array $fields
     * @return bool
     * @throws AchValidationException
     */
    public function validateDataTypes(array $fields): bool
    {
        $fields = array_filter($fields, fn ($field) => (! array_key_exists('blank', $field) || ! $field['blank']) && $field['value'] !== '');

        foreach ($fields as $field) {
            switch ($field['type']) {
                case FieldTypes::TYPE_NUMERIC:
                    $this->testRegex(self::$numericRegex, $field);
                    break;
                case FieldTypes::TYPE_ALPHA:
                    $this->testRegex(self::$alphaRegex, $field);
                    break;
                case FieldTypes::TYPE_ALPHANUMERIC:
                    $this->testRegex(self::$alphanumericRegex, $field);
                    break;
            }
        }

        return true;
    }

    /**
     * Validate addenda type codes.
     *
     * @param $code
     * @return bool
     * @throws AchValidationException
     */
    public function validateAddendaTypeCode($code): bool
    {
        $code = (string) $code;

        if (strlen($code) !== 2 || ! in_array($code, self::ADDENDA_TYPE_CODES)) {
            throw new AchValidationException("The ACH addenda type code '{$code}' is invalid. Please pass a valid 2-digit type code.");
        }

        return true;
    }

    /**
     * Validate addenda transaction codes.
     *
     * @param $code
     * @return bool
     * @throws AchValidationException
     */
    public function validateAddendaTransactionCode($code): bool
    {
        $code = (string) $code;

        if (strlen($code) !== 2 || ! in_array($code, self::ADDENDA_TRANSACTION_CODES)) {
            throw new AchValidationException("The ACH transaction type code '{$code}' is invalid. Please pass a valid 2-digit transaction code.");
        }

        return true;
    }

    /**
     * Validate service class codes.
     *
     * @param $code
     * @return bool
     * @throws AchValidationException
     */
    public function validateServiceClassCode($code): bool
    {
        $code = (string) $code;

        if (strlen($code) !== 3 || ! in_array($code, self::SERVICE_CLASS_CODES)) {
            throw new AchValidationException("The ACH service class code '{$code}' is invalid. Please pass a valid 3-digit service class code.");
        }

        return true;
    }

    /**
     * Validate ABA routing numbers.
     *
     * @param $number
     * @return bool
     * @throws AchValidationException
     */
    public function validateRoutingNumber($number): bool
    {
        $number = preg_replace('/[^0-9]/', '', $number);
        $number = str_pad($number, 9, '0', STR_PAD_LEFT);
        $length = strlen($number);

        if ($length !== 9) {
            throw new AchValidationException("The ABA routing number '{$number}' is {$length}-digits long, but it should be 9-digits long.");
        }

        if (Utils::createChecksum($number) % 10 !== 0) {
            throw new AchValidationException("The ABA routing number {$number} is invalid. Please ensure a valid 9-digit ABA routing number is passed.");
        }

        return true;
    }

    /**
     * Perform regex test.
     *
     * @param $regex
     * @param array $field
     * @return bool
     * @throws AchValidationException
     */
    protected function testRegex($regex, array $field): bool
    {
        $subject = $field['value'];

        if (array_key_exists('number', $field)) {
            $subject = number_format((float) $subject, 2, '', '');
        }

        if (! preg_match($regex, $subject)) {
            throw new AchValidationException("Invalid data type: {$field['name']} is not a an expected {$field['type']}: {$field['value']}");
        }

        return true;
    }
}
