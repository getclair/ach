<?php

namespace Clair\Ach;

use Clair\Ach\Definitions\Batch as BatchDefinition;
use Clair\Ach\Support\Utils;
use Illuminate\Support\Arr;

class Batch extends AchObject
{
    public const CREDIT_CODES = ['22', '23', '24', '32', '33', '34'];
    public const DEBIT_CODES = ['27', '28', '29', '37', '38', '39'];

    /**
     * @var array
     */
    protected array $entries = [];

    /**
     * @var array
     */
    protected array $header = [];

    /**
     * @var array
     */
    protected array $control = [];

    /**
     * @var array|string[]
     */
    protected array $overrides = [
        'header' => ['serviceClassCode', 'companyDiscretionaryData', 'companyIdentification', 'standardEntryClassCode'],
        'control' => ['addendaCount', 'entryHash', 'totalDebit', 'totalCredit'],
    ];

    /**
     * File constructor.
     * @param array $options
     * @param false $autoValidate
     * @throws Exceptions\AchValidationException
     */
    public function __construct(array $options, bool $autoValidate = true)
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
        $count = $this->getControlValue('addendaCount') + $entry->getRecordCount();

        $this->setControlValue('addendaCount', $count);

        $this->entries[] = $entry;

        $entryHash = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($this->entries as $entry) {
            $entryHash[] = (int) $entry->getFieldValue('receivingDFI');

            $transactionCode = (string) $entry->getFieldValue('transactionCode');

            if (in_array($transactionCode, self::CREDIT_CODES)) {
                $totalCredit += (int) $entry->getFieldValue('amount');
            }

            if (in_array($transactionCode, self::DEBIT_CODES)) {
                $totalDebit += (int) $entry->getFieldValue('amount');
            }
        }

        $this->setControlValue('totalCredit', $totalCredit);
        $this->setControlValue('totalDebit', $totalDebit);

        // Add up the positions 4-11 and compute the total. Slice the 10 rightmost digits.
        $this->setControlValue('entryHash', array_sum(array_slice($entryHash, 0, 10)));
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
     * @return string
     */
    public function generateHeader(): string
    {
        return Utils::generateString($this->header);
    }

    /**
     * Generate control as a string.
     *
     * @return string
     */
    public function generateControl(): string
    {
        return Utils::generateString($this->control);
    }

    /**
     * Generate entries as a string.
     *
     * @return string
     */
    public function generateEntries(): string
    {
        $results = [];

        foreach ($this->entries as $entry) {
            $results[] = $entry->generateString();
        }

        return implode(Utils::NEWLINE, $results);
    }

    /**
     * Generate batch as a string.
     *
     * @return string
     */
    public function generateString(): string
    {
        return implode(Utils::NEWLINE, [
            $this->generateHeader(),
            $this->generateEntries(),
            $this->generateControl(),
        ]);
    }

    /**
     * Get batch header.
     *
     * @return array
     */
    public function getHeader(): array
    {
        return $this->header;
    }

    /**
     * Set batch header.
     */
    protected function setHeader()
    {
        $this->header = array_merge(Arr::get($this->options, 'header', []), BatchDefinition::$header);
    }

    /**
     * Set batch control.
     */
    protected function setControl()
    {
        $this->control = array_merge(Arr::get($this->options, 'control', []), BatchDefinition::$control);
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
        foreach (['serviceClassCode', 'companyIdentification', 'originatorStatusCode', 'originatingDFI', 'effectiveEntryDate', 'settlementDate'] as $key) {
            if (array_key_exists($key, $this->options)) {
                $value = $this->options[$key];

                if ($key === 'originatingDFI') {
                    $value = substr(Utils::addCheckDigit($this->options['originatingDFI']), 0, $this->header[$key]['width']);
                }

                if ($key === 'effectiveEntryDate') {
                    $this->options[$key] = Utils::parseDate($this->options['effectiveEntryDate']);
                    $value = Utils::formatDate($this->options[$key]);
                }

                $this->setHeaderValue($key, $value);
                $this->setControlValue($key, $this->getHeaderValue($key));
            }
        }
    }
}
