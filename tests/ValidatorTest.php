<?php

namespace Clair\Ach\Tests;

use Clair\Ach\Definitions\FieldTypes;
use Clair\Ach\Exceptions\AchValidationException;
use Clair\Ach\Validator;

class ValidatorTest extends TestCase
{
    /**
     * @dataProvider providesRequiredData
     */
    public function test_checks_for_required_fields($value, bool $is_valid)
    {
        if (! $is_valid) {
            $this->expectException(AchValidationException::class);
            $this->expectExceptionMessage('Missing required field: Item');
        }

        $fields = [
            [
                'name' => 'Item',
                'width' => 1,
                'position' => 1,
                'required' => true,
                'value' => $value,
            ],
        ];

        $validator = new Validator();

        $result = $validator->validateRequiredFields($fields);

        if ($is_valid) {
            $this->assertTrue($result);
        }
    }

    public static function providesRequiredData(): array
    {
        return [
            'present' => ['valid', true],
            'empty' => ['', false],
            'null' => [null, false],
        ];
    }

    /**
     * @dataProvider providesLengthData
     */
    public function test_checks_for_required_lengths($width, $value, bool $is_valid)
    {
        if (! $is_valid) {
            $length = strlen($value);

            $this->expectException(AchValidationException::class);
            $this->expectExceptionMessage("Invalid length: Item's length is {$length} but should be no greater than $width");
        }

        $fields = [
            array_filter([
                'name' => 'Item',
                'width' => $width,
                'position' => 1,
                'value' => $value,
            ]),
        ];

        $validator = new Validator();

        $result = $validator->validateLengths($fields);

        if ($is_valid) {
            $this->assertTrue($result);
        }
    }

    public static function providesLengthData(): array
    {
        return [
            'valid' => [6, 'string', true],
            'invalid, long' => [6, 'stringy', false],
            'valid, short' => [6, 'strin', true],
            'valid, no width' => [null, 'string', true],
        ];
    }

    /**
     * @dataProvider providesDataTypesData
     */
    public function test_checks_for_valid_data_types($type, $value, bool $is_valid)
    {
        if (! $is_valid) {
            $this->expectException(AchValidationException::class);
            $this->expectExceptionMessage("Invalid data type: Item is not a an expected {$type}");
        }

        $fields = [
            array_filter([
                'name' => 'Item',
                'width' => 10,
                'position' => 1,
                'value' => $value,
                'type' => $type,
                'number' => $type === FieldTypes::TYPE_NUMERIC,
            ]),
        ];

        $validator = new Validator();

        $result = $validator->validateDataTypes($fields);

        if ($is_valid) {
            $this->assertTrue($result);
        }
    }

    public static function providesDataTypesData(): array
    {
        return [
            'valid alpha' => [FieldTypes::TYPE_ALPHA, 'string', true],
            'invalid alpha' => [FieldTypes::TYPE_ALPHA, 'str1ng', false],
            'valid alphanumeric' => [FieldTypes::TYPE_ALPHANUMERIC, 'a1b2c3!<>!*$&@(^', true],
            'invalid alphanumeric' => [FieldTypes::TYPE_ALPHANUMERIC, 'a1b2c3 ™®©', false],
            'valid numeric' => [FieldTypes::TYPE_NUMERIC, '123456', true],
            'invalid numeric' => [FieldTypes::TYPE_NUMERIC, '123456a', true], // it gets formatted nicely
        ];
    }

    /**
     * @dataProvider providesAddendaTypeCodeData
     */
    public function test_checks_for_valid_addenda_type_code($code, bool $is_valid)
    {
        if (! $is_valid) {
            $this->expectException(AchValidationException::class);
            $this->expectExceptionMessage("The ACH addenda type code '{$code}' is invalid. Please pass a valid 2-digit type code.");
        }

        $validator = new Validator();

        $result = $validator->validateAddendaTypeCode($code);

        if ($is_valid) {
            $this->assertTrue($result);
        }
    }

    public static function providesAddendaTypeCodeData(): array
    {
        return [
            'valid code, string' => ['99', true],
            'valid code, string, leading zero' => ['02', true],
            'valid code, number' => [99, true],
            'invalid code, length' => [999, false],
            'invalid code, value' => [97, false],
        ];
    }

    /**
     * @dataProvider providesAddendaTransactionCodeData
     */
    public function test_checks_for_valid_addenda_transaction_code($code, bool $is_valid)
    {
        if (! $is_valid) {
            $this->expectException(AchValidationException::class);
            $this->expectExceptionMessage("The ACH transaction type code '{$code}' is invalid. Please pass a valid 2-digit transaction code.");
        }

        $validator = new Validator();

        $result = $validator->validateAddendaTransactionCode($code);

        if ($is_valid) {
            $this->assertTrue($result);
        }
    }

    public static function providesAddendaTransactionCodeData(): array
    {
        return [
            'valid code, string' => ['22', true],
            'valid code, number' => [22, true],
            'invalid code, length' => [222, false],
            'invalid code, value' => [20, false],
        ];
    }

    /**
     * @dataProvider providesServiceTypeCodeData
     */
    public function test_checks_for_valid_service_type_code($code, bool $is_valid)
    {
        if (! $is_valid) {
            $this->expectException(AchValidationException::class);
            $this->expectExceptionMessage("The ACH service class code '{$code}' is invalid. Please pass a valid 3-digit service class code.");
        }

        $validator = new Validator();

        $result = $validator->validateServiceClassCode($code);

        if ($is_valid) {
            $this->assertTrue($result);
        }
    }

    public static function providesServiceTypeCodeData(): array
    {
        return [
            'valid code, string' => ['200', true],
            'valid code, number' => [200, true],
            'invalid code, length' => [2000, false],
            'invalid code, value' => [20, false],
        ];
    }

    /**
     * @dataProvider providesRoutingNumberData
     */
    public function test_checks_for_valid_routing_number($number, bool $is_correct_length, bool $is_valid)
    {
        if (! $is_correct_length) {
            $length = strlen($number);
            $this->expectException(AchValidationException::class);
            $this->expectExceptionMessage("The ABA routing number '{$number}' is {$length}-digits long, but it should be 9-digits long.");
        }

        if (! $is_valid) {
            $number = str_pad($number, 9, '0', STR_PAD_LEFT);
            $this->expectException(AchValidationException::class);
            $this->expectExceptionMessage("The ABA routing number {$number} is invalid. Please ensure a valid 9-digit ABA routing number is passed.");
        }

        $validator = new Validator();

        $result = $validator->validateRoutingNumber($number);

        if ($is_correct_length || $is_valid) {
            $this->assertTrue($result);
        }
    }

    public static function providesRoutingNumberData(): array
    {
        return [
            'valid routing number, string' => ['021000021', true, true],
            'invalid routing number, string' => ['021000022', true, false],
            'invalid routing number, number' => [021000021, true, false], // this becomes 004456465
            'invalid routing number, long' => ['12345678901', false, true],
            'invalid routing number, short' => ['777777', true, false],
        ];
    }
}
