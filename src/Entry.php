<?php

namespace Clair\Ach;

use Clair\Ach\Definitions\Entry as EntryDefinition;
use Clair\Ach\Support\Utils;
use Illuminate\Support\Arr;

class Entry extends AchObject
{
    /**
     * @var array
     */
    protected array $addendas = [];

    /**
     * @var array
     */
    protected array $fields = [];

    /**
     * @var array|string[]
     */
    protected array $overrides = [
        'fields' => ['transactionCode', 'receivingDFI', 'checkDigit', 'DFIAccount', 'amount', 'idNumber', 'individualName', 'discretionaryData', 'addendaId', 'traceNumber'],
    ];

    /**
     * Entry constructor.
     * @param array $options
     * @param bool $autoValidate
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
     * Return fields.
     *
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Add a new Entry Addenda.
     *
     * @param Addenda $addenda
     */
    public function addAddenda(Addenda $addenda)
    {
        // Set addendaId to 1 if there's any addenda
        $this->setFieldValue('addendaId', '1');

        $addenda->setFieldValue('entryDetailSequenceNumber', $this->getFieldValue('traceNumber'));

        if (! $addenda->getFieldValue('addendaSequenceNumber')) {
            $addenda->setFieldValue('addendaSequenceNumber', $this->getRecordCount());
        }

        $this->addendas[] = $addenda;
    }

    /**
     * Return addendas.
     *
     * @return array
     */
    public function getAddendas(): array
    {
        return $this->addendas;
    }

    /**
     * Return record count.
     *
     * @return int
     */
    public function getRecordCount(): int
    {
        return count($this->getAddendas()) + 1;
    }

    /**
     * Generate entry as string.
     *
     * @return string
     */
    public function generateString(): string
    {
        $results = [Utils::generateString($this->fields)];

        foreach ($this->addendas as $addenda) {
            $results[] = $addenda->generateString();
        }

        return implode(Utils::NEWLINE, $results);
    }

    /**
     * Validate the fields.
     *
     * @return bool
     * @throws Exceptions\AchValidationException
     */
    public function validate(): bool
    {
        $validator = new Validator();

        if ($this->getFieldValue('addendaId') === '1') {
            $validator->validateAddendaTransactionCode($this->fields['transactionCode']['value']);
        }

        $validator->validateRequiredFields($this->fields);
        $validator->validateRoutingNumber(Utils::addCheckDigit($this->fields['receivingDFI']['value']));
        $validator->validateLengths($this->fields);
        $validator->validateDataTypes($this->fields);

        return true;
    }

    /**
     * Set the initial fields.
     */
    protected function setFields()
    {
        $this->fields = array_merge(Arr::get($this->options, 'fields', []), EntryDefinition::$fields);
    }

    /**
     * Set any initial values.
     */
    protected function setValues()
    {
        // Set sub-strings
        foreach (['DFIAccount', 'individualName'] as $key) {
            if (array_key_exists($key, $this->options)) {
                $this->setFieldValue($key, substr($this->options[$key], 0, $this->fields[$key]['width']));
            }
        }

        // Set explicit values
        foreach (['amount', 'idNumber', 'discretionaryData', 'receivingDFI'] as $key) {
            if (array_key_exists($key, $this->options)) {
                if ($key === 'receivingDFI') {
                    $this->setFieldValue('checkDigit', Utils::computeCheckDigit($this->options[$key]));
                }

                $this->setFieldValue($key, $this->options[$key]);
            }
        }
    }
}
