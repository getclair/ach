<?php

namespace Clair\Ach;

use Clair\Ach\Definitions\Batch as BatchDefinition;
use Clair\Ach\Support\Utils;
use Closure;
use Illuminate\Support\Arr;

class Batch extends AchObject
{
    public const CREDIT_CODES = ['22', '23', '24', '32', '33', '34'];
    public const DEBIT_CODES = ['27', '28', '29', '37', '38', '39'];

    protected array $options;

    protected array $entries = [];

    protected array $header = [];

    protected array $control = [];

    protected array $highLevelHeaderOverrides = [
        'serviceClassCode', 'companyDiscretionaryData', 'companyIdentification', 'standardEntryClassCode',
    ];

    protected array $highLevelControlOverrides = [
        'addendaCount', 'entryHash', 'totalDebit', 'totalCredit',
    ];

    /**
     * File constructor.
     * @param array $options
     * @param false $autoValidate
     */
    public function __construct(array $options, $autoValidate = true)
    {
        $this->options = $options;

        $this->boot();

        if ($autoValidate) {
            $this->validate();
        }
    }

    /**
     * Validate batch header and control values.
     *
     * @return bool
     * @throws Exceptions\AchValidationException
     */
    public function validate(): bool
    {
        $validator = new Validator();

        // Attach checksum to 8-digit ABA number.
        $validator->validateRoutingNumber(
            $this->options['originatingDFI'].Utils::computeCheckDigit($this->options['originatingDFI'])
        );

        $validator->validateRequiredFields($this->header);
        $validator->validateLengths($this->header);
        $validator->validateDataTypes($this->header);

        $validator->validateRequiredFields($this->control);
        $validator->validateLengths($this->control);
        $validator->validateDataTypes($this->control);

        $validator->validateServiceClassCode($this->getHeaderValue('serviceClassCode'));

        return true;
    }

    /**
     * Add an entry.
     *
     * @param Entry $entry
     */
    public function addEntry(Entry $entry)
    {
        $this->control['addendaCount'] += $entry->getRecordCount();

        $this->entries[] = $entry;

        $entryHash = 0;
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($this->entries as $entry) {
            $entryHash += (int) $entry->getFieldValue('receivingDFI');

            if (in_array($entry->getFieldValue('transactionCode'), self::CREDIT_CODES)) {
                $totalCredit += $entry->getFieldValue('amount');
                break;
            } elseif (in_array($entry->getFieldValue('transactionCode'), self::DEBIT_CODES)) {
                $totalDebit += $entry->getFieldValue('amount');
                break;
            }
        }

        $this->setControlValue('totalCredit', $totalCredit);
        $this->setControlValue('totalDebit', $totalDebit);

        // Add up the positions 4-11 and compute the total. Slice the 10 rightmost digits.
        $this->setControlValue('entryHash', substr($entryHash, -10));
    }

    /**
     * Return batch entries.
     *
     * @return array
     */
    public function getEntries(): array
    {
        return $this->entries;
    }

    /**
     * Generate header as a string.
     *
     * @param Closure $callback
     */
    public function generateHeader()
    {
        return Utils::generateString($this->header);
    }

    /**
     * Generate control as a string.
     *
     * @param Closure $callback
     */
    public function generateControl()
    {
        return Utils::generateString($this->control);
    }

    /**
     * Generate entries as a string.
     *
     * @param Closure $callback
     */
    public function generateEntries()
    {
        $result = '';

        foreach ($this->entries as $entry) {
            $result .= $entry->generateString();
            $result .= Utils::NEWLINE;
        }

        return $result;
    }

    /**
     * Generate batch as a string.
     *
     * @return string
     */
    public function generateString(): string
    {
        return implode('', [
            $this->generateHeader(),
            Utils::NEWLINE,
            $this->generateEntries(),
            $this->generateControl(),
        ]);
    }

    /**
     * Boot the batch.
     *
     * @return mixed|void
     */
    protected function boot()
    {
        $this->setHeader();
        $this->setControl();
        $this->setOverrides();
        $this->setValues();
    }

    /**
     * Set batch header.
     */
    protected function setHeader()
    {
        $this->header = array_merge(Arr::get($this->options, 'header', []), BatchDefinition::$headers);
    }

    /**
     * Set batch control.
     */
    protected function setControl()
    {
        $this->control = array_merge(Arr::get($this->options, 'control', []), BatchDefinition::$controls);
    }

    /**
     * Set batch values.
     */
    protected function setValues()
    {
        // Set sub-strings
        foreach (['companyName', 'companyEntryDescription', 'companyDescriptiveDate'] as $key) {
            if (array_key_exists($key, $this->options)) {
                $this->setHeaderValue($key, substr($this->options[$key], 0, $this->header[$key]['width']));
            }
        }

        // Set explicit values
        foreach (['serviceClassCode', 'companyIdentification', 'originatingDFI'] as $key) {
            if (array_key_exists($key, $this->options)) {
                $this->setHeaderValue($key, $this->cast($this->options[$key], $this->header[$key]['type']));
                $this->setControlValue($key, $this->cast($this->options[$key], $this->control[$key]['type']));
            }
        }

        // Set custom values
        if (array_key_exists('effectiveEntryDate', $this->options)) {
            $this->setHeaderValue('effectiveEntryDate', Utils::formatDate($this->options['effectiveEntryDate']));
        }

        if (array_key_exists('originatingDFI', $this->options)) {
            $this->setHeaderValue(
                'originatingDFI',
                substr(Utils::computeCheckDigit($this->options['originatingDFI']), 0, $this->header[$key]['width'])
            );
        }
    }

    /**
     * Set batch overrides.
     */
    protected function setOverrides()
    {
        foreach ($this->highLevelHeaderOverrides as $key) {
            if (array_key_exists($key, $this->options)) {
                $this->setHeaderValue($key, $this->options[$key]);
            }
        }

        foreach ($this->highLevelControlOverrides as $key) {
            if (array_key_exists($key, $this->options)) {
                $this->setControlValue($key, $this->options[$key]);
            }
        }
    }
}
