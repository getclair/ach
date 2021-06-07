<?php

namespace Clair\Ach;

use Clair\Ach\Definitions\Addenda as AddendaDefinition;
use Clair\Ach\Support\Utils;
use Illuminate\Support\Arr;

class Addenda extends AchObject
{
    /**
     * @var array
     */
    protected array $fields = [];

    /**
     * @var array|string[]
     */
    protected array $overrides = [
        'fields' => ['addendaTypeCode', 'paymentRelatedInformation', 'addendaSequenceNumber', 'entryDetailSequenceNumber'],
    ];

    /**
     * Addenda constructor.
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
     * Validate the fields.
     *
     * @return bool
     * @throws Exceptions\AchValidationException
     */
    public function validate(): bool
    {
        $validator = new Validator();

        $validator->validateRequiredFields($this->fields);
        $validator->validateAddendaTypeCode($this->getFieldValue('addendaTypeCode'));
        $validator->validateLengths($this->fields);
        $validator->validateDataTypes($this->fields);

        return true;
    }

    /**
     * Generate addenda as string.
     *
     * @return string
     */
    public function generateString(): string
    {
        return Utils::generateString($this->fields);
    }

    /**
     * Set the initial fields.
     */
    protected function setFields()
    {
        $this->fields = array_merge(Arr::get($this->options, 'fields', []), AddendaDefinition::$fields);
    }

    /**
     * Set any initial values.
     */
    protected function setValues()
    {
        // Set sub-strings
        foreach (['returnCode', 'paymentRelatedInformation'] as $key) {
            if (array_key_exists($key, $this->options)) {
                $this->setFieldValue($key, substr($this->options[$key], 0, $this->fields[$key]['width']));
            }
        }

        // Set explicit values
        foreach (['addendaSequenceNumber'] as $key) {
            if (array_key_exists($key, $this->options)) {
                $this->setFieldValue($key, $this->options[$key]);
            }
        }

        if (array_key_exists('entryDetailSequenceNumber', $this->options)) {
            $this->setFieldValue('entryDetailSequenceNumber', substr($this->options['entryDetailSequenceNumber'], 0 - $this->fields['entryDetailSequenceNumber']['width']));
        }
    }

    /**
     * @param $key
     * @param $value
     */
    public function setFieldValue($key, $value)
    {
        if ($key === 'entryDetailSequenceNumber') {
            $value = substr($value, 0 - $this->fields[$key]['width']);
        }

        parent::setFieldValue($key, $value);
    }
}
