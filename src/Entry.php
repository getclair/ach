<?php

namespace Clair\Ach;

use Clair\Ach\Dictionaries\Entry as EntryDictionary;
use Illuminate\Support\Arr;

class Entry extends AchObject
{
    /**
     * @var array
     */
    protected array $options;

    /**
     * @var array
     */
    protected array $addendas = [];

    /**
     * @var array|string[]
     */
    protected array $highLevelOverrides = [
        'transactionCode', 'receivingDFI', 'checkDigit', 'DFIAccount', 'amount', 'idNumber', 'individualName', 'discretionaryData', 'addendaId', 'traceNumber',
    ];

    /**
     * Entry constructor.
     * @param array $options
     * @param bool $autoValidate
     */
    public function __construct(array $options, bool $autoValidate = false)
    {
        $this->options = $options;

        $this->boot();

        if ($autoValidate) {
            $this->validate();
        }
    }

    /**
     * Add a new Entry Addenda.
     *
     * @param Addenda $addenda
     */
    public function addAddenda(Addenda $addenda)
    {
        $this->setFieldValue('addendaId', '1');

        $addenda->setFieldValue('addendaSequenceNumber', $this->getRecordCount());
        $addenda->setFieldValue('entryDetailSequenceNumber', $this->getFieldValue('traceNumber'));

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
        return count($this->addendas) + 1;
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

        $validator->validateRequiredFields($this->fields);

        if (Arr::get($this->fields, 'addendaId.value') === '0') {
            $validator->validateAddendaTransactionCode($this->fields['transactionCode']['value']);
        }

        $validator->validateRoutingNumber($this->fields['receivingDFI']['value'] + $this->fields['checkDigit']['value']);

        $validator->validateLengths($this->fields);

        $validator->validateDataTypes($this->fields);

        return true;
    }

    /**
     * Boot the entry by setting configuration and values.
     */
    protected function boot()
    {
        $this->setFields();
        $this->setOverrides();
        $this->setValues();
        $this->validate();
    }

    /**
     * Set the initial fields.
     */
    protected function setFields()
    {
        $this->fields = array_merge(Arr::get($this->options, 'fields', []), EntryDictionary::$fields);
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
        foreach (['amount', 'idNumber', 'discretionaryData'] as $key) {
            if (array_key_exists($key, $this->options)) {
                $this->setFieldValue($key, settype($this->options[$key], $this->options[$key]['type'] ?? 'string'));
            }
        }
    }
}
