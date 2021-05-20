<?php

namespace Clair\Ach\Support;

use Carbon\Carbon;
use Clair\Ach\Definitions\FieldTypes;
use Illuminate\Support\Arr;

class Utils
{
    public const NEWLINE = "\n";
    public const ACH_DATE_FORMAT = 'ymd';
    public const ACH_TIME_FORMAT = 'Hi';

    /**
     * Return a string at the appropriate length.
     *
     * @param $string
     * @param $object
     * @return array
     */
    public static function parseLine($string, array $object): array
    {
        $result = [];
        $position = 0;

        foreach (array_keys($object) as $key) {
            $field = $object[$key];
            $result[$key] = substr($string, $position, $field['width']);
            $position += $field['width'];
        }

        return $result;
    }

    /**
     * Create checksum.
     *
     * Validate the routing number (ABA). See here for more info: http://www.brainjar.com/js/validation/
     * @param $aba_number
     * @return int|null
     */
    public static function createChecksum($aba_number)
    {
        $aba_number = substr($aba_number, 0, 9);
        $numbers = array_map(fn ($digit) => (int) $digit, str_split($aba_number));

        if (count($numbers) !== 9) {
            return null;
        }

        return
            3 * ($numbers[0] + $numbers[3] + $numbers[6]) +
            7 * ($numbers[1] + $numbers[4] + $numbers[7]) +
            1 * ($numbers[2] + $numbers[5] + $numbers[8]);
    }

    /**
     * Compute check digit.
     *
     * @param $aba_number
     * @return int|null
     */
    public static function computeCheckDigit($aba_number)
    {
        $aba_number = substr($aba_number, 0, 8);
        $numbers = array_map(fn ($digit) => (int) $digit, str_split($aba_number));

        if (count($numbers) !== 8) {
            return 0;
        }

        $total = (
            7 * ($numbers[0] + $numbers[3] + $numbers[6]) +
            3 * ($numbers[1] + $numbers[4] + $numbers[7]) +
            9 * ($numbers[2] + $numbers[5])
        );

        return $total % 10;
    }

    /**
     * Return ABA number with check digit.
     *
     * @param $aba_number
     * @return string
     */
    public static function addCheckDigit($aba_number): string
    {
        return $aba_number.self::computeCheckDigit($aba_number);
    }

    /**
     * Create a valid ACH date in YYMMDD format.
     * @param null $date
     * @return string
     */
    public static function formatDate($date = null): string
    {
        return Carbon::parse($date)->format(self::ACH_DATE_FORMAT);
    }

    /**
     * Create a valid ACH time in HHMM format.
     * @param null $date
     * @return string
     */
    public static function formatTime($date = null): string
    {
        return Carbon::parse($date)->format(self::ACH_TIME_FORMAT);
    }

    /**
     * Parse an ACH date into a string.
     * @param null $date
     * @return string
     */
    public static function parseDate($date): string
    {
        return Carbon::createFromFormat(self::ACH_DATE_FORMAT, $date)->toDateString();
    }

    /**
     * Produce a string based on field values.
     *
     * @param array $fields
     * @return string
     */
    public static function generateString(array $fields): string
    {
        $counter = 0;
        $fieldCount = count($fields);
        $result = '';

        while ($counter <= $fieldCount) {
            foreach ($fields as $field) {
                if ($field['position'] === $counter) {
                    $result .= self::formatFieldValue($field);
                }
            }

            $counter++;
        }

        return $result;
    }

    /**
     * Return next multiple value.
     *
     * @param int $value
     * @param int $multiple
     * @return int|mixed
     */
    public static function getNextMultiple(int $value, int $multiple)
    {
        return $value % $multiple === 0 ? $value : $value + ($multiple - $value % $multiple);
    }

    /**
     * Return next multiple value diff.
     *
     * @param int $value
     * @param int $multiple
     * @return int|mixed
     */
    public static function getNextMultipleDiff(int $value, int $multiple)
    {
        return self::getNextMultiple($value, $multiple) - $value;
    }

    /**
     * Format field values.
     *
     * @param $field
     * @return string
     */
    protected static function formatFieldValue($field): string
    {
        $value = $field['value'];
        $width = $field['width'];

        if (Arr::get($field, 'blank') === true || Arr::get($field, 'type') === FieldTypes::TYPE_ALPHANUMERIC) {
            return str_pad($value, $width);
        }

        if (isset($field['number'])) {
            $value = number_format((float) $value, 2, '', '');
        }

        $character = array_key_exists('paddingChar', $field) ? $field['paddingChar'] : '0';

        return str_pad($value, $width, $character, STR_PAD_LEFT);
    }
}
