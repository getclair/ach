<?php

namespace Clair\Ach\Support;

use Carbon\Carbon;
use Clair\Ach\Dictionaries\AbstractDictionary;
use Illuminate\Support\Arr;

class Utils
{
    public const NEWLINE = "\r\n";

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
            $result[$key] = trim(substr($string, $position, $field['width']));
            $position += $field['width'];
        }

        return $result;
    }

    /**
     * Compute check digit.
     *
     * @param $number
     * @return int|mixed
     */
    public static function computeCheckDigit($number)
    {
        $numbers = array_map(fn ($digit) => (int) $digit, str_split($number));

        return count($numbers) !== 8
            ? $number
            : $number + (self::createChecksum($numbers)) % 10;
    }

    /**
     * Create checksum.
     *
     * Validate the routing number (ABA). See here for more info: http://www.brainjar.com/js/validation/
     * @param array $numbers
     * @return float|int
     */
    public static function createChecksum(array $numbers)
    {
        return
            3 * ($numbers[0] + $numbers[3] + $numbers[6]) +
            7 * ($numbers[1] + $numbers[4] + $numbers[7]) +
            1 * ($numbers[2] + $numbers[5] + $numbers[8]);
    }

    /**
     * Create a valid ACH date in YYMMDD format.
     * @param null $date
     * @return string
     */
    public static function formatDate($date = null)
    {
        return Carbon::parse($date)->format('ymd');
    }

    /**
     * Create a valid ACH time in HHMM format.
     * @param null $date
     * @return string
     */
    public static function formatTime($date = null)
    {
        return Carbon::parse($date)->format('Hi');
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

        while ($counter < $fieldCount) {
            foreach ($fields as $field) {
                if ($field['position'] === $counter) {
                    if (Arr::get($field, 'blank') === true || Arr::get($field, 'type') === AbstractDictionary::TYPE_ALPHANUMERIC) {
                        $result .= str_pad($field['value'], $field['width']);
                    } else {
                        $value = $field['number'] ? number_format((float) $field['value'], 2, '', '') : $field['value'];
                        $character = Arr::get($field, 'paddingChar', '0');
                        $result .= str_pad($value, $field['width'], $character, STR_PAD_LEFT);
                    }
                }
            }

            $counter++;
        }

        return $result;
    }

    /**
     * Return next multiple value.
     *
     * @param $value
     * @param $multiple
     * @return int|mixed
     */
    public static function getNextMultiple($value, $multiple)
    {
        return $value % $multiple == 0
            ? $value
            : $value + ($multiple - $value % $multiple);
    }

    /**
     * Return next multiple value diff.
     *
     * @param $value
     * @param $multiple
     * @return int|mixed
     */
    public static function getNextMultipleDiff($value, $multiple)
    {
        return self::getNextMultiple($value, $multiple) - $value;
    }
}
