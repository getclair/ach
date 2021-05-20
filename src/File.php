<?php

namespace Clair\Ach;

use Clair\Ach\Definitions\File as FileDefinition;
use Clair\Ach\Support\HandlesValues;
use Clair\Ach\Support\Utils;
use Illuminate\Support\Arr;

class File extends AchObject
{
    /**
     * @var array
     */
    protected array $batches = [];

    /**
     * @var array
     */
    protected array $header = [];

    /**
     * @var array
     */
    protected array $control = [];

    /**
     * @var int
     */
    protected int $batchSequenceNumber = 0;

    /**
     * @var array|string[]
     */
    protected array $highLevelOverrides = [
        'immediateDestination', 'immediateOrigin', 'fileCreationDate', 'fileCreationTime', 'fileIdModifier', 'immediateDestinationName', 'immediateOriginName', 'referenceCode',
    ];

    /**
     * File constructor.
     * @param array $options
     * @param false $autoValidate
     */
    public function __construct(FileOptions $options, $autoValidate = true)
    {
        $this->options = $options->toArray();

        $this->boot();

        if ($autoValidate) {
            $this->validate();
        }
    }

    /**
     * Validate the file contents.
     *
     * @return bool
     * @throws Exceptions\AchValidationException
     */
    public function validate(): bool
    {
        $validator = new Validator();

        $validator->validateLengths($this->header);
        $validator->validateDataTypes($this->header);
        $validator->validateLengths($this->control);
        $validator->validateDataTypes($this->control);

        return true;
    }

    /**
     * Add a batch to the file.
     *
     * @param Batch $batch
     */
    public function addBatch(Batch $batch)
    {
        $batch->setHeaderValue('batchNumber', $this->batchSequenceNumber);
        $batch->setControlValue('batchNumber', $this->batchSequenceNumber);

        $this->batchSequenceNumber++;

        $this->batches[] = $batch;
    }

    /**
     * Return the file batches.
     *
     * @return array
     */
    public function getBatches(): array
    {
        return $this->batches;
    }

    /**
     * @param $rows
     * @return string
     */
    public function generatePaddedRows($rows): string
    {
        $result = '';

        for ($i = 0; $i < $rows; $i++) {
            $result .= Utils::NEWLINE.str_pad('', 94, '9');
        }

        return $result;
    }

    /**
     * Generate file batches.
     *
     * @return array
     */
    public function generateBatches(): array
    {
        $result = '';
        $rows = 2;

        $entryHash = 0;
        $addendaCount = 0;

        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($this->batches as $batch) {
            $totalDebit += $batch->getControlValue('totalDebit');
            $totalCredit += $batch->getControlValue('totalCredit');

            foreach ($batch->getEntries() as $entry) {
                $entry->setFieldValue(
                    'traceNumber',
                    $entry->getFieldValue('traceNumber')
                        ? $entry->getFieldValue('traceNumber')
                        : substr($this->getHeaderValue('immediateOrigin'), 0, 8).str_pad($addendaCount, 7, '0', STR_PAD_LEFT)
                );

                $entryHash += (int) $entry->getFieldValue('receivingDFI');

                $addendaCount++;
                $rows++;
            }

            if (count($batch->getEntries()) > 0) {
                $this->incrementControlValue('batchCount');

                $rows = $rows + 2;

                $string = $batch->generateString();
                $result .= $string.Utils::NEWLINE;
            }
        }

        $this->setControlValue('totalDebit', $totalDebit);
        $this->setControlValue('totalCredit', $totalCredit);
        $this->setControlValue('addendaCount', $addendaCount);
        $this->setControlValue('blockCount', Utils::getNextMultiple($rows, 10) / 10);
        $this->setControlValue('entryHash', substr($entryHash, -10));

        return [$result, $rows];
    }

    /**
     * @return string
     */
    public function generateHeader(): string
    {
        return Utils::generateString($this->header);
    }

    /**
     * @return string
     */
    public function generateControl(): string
    {
        return Utils::generateString($this->control);
    }

    /**
     * Generate the file.
     *
     * @return string
     */
    public function generateFile(): string
    {
        $headerString = $this->generateHeader();
        $controlString = $this->generateControl();
        [$batchString, $rows] = $this->generateBatches();
        $paddedRows = Utils::getNextMultipleDiff($rows, 10);

        $paddedString = $this->generatePaddedRows($paddedRows);

        return implode('', [$headerString, Utils::NEWLINE, $batchString, $controlString, $paddedString]);
    }

    /**
     * Boot the file object.
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
     * Set header values.
     */
    protected function setHeader()
    {
        $this->header = array_merge(Arr::get($this->options, 'header', []), FileDefinition::$headers);

        $this->header['fileCreationDate']['value'] = Utils::formatDate();
        $this->header['fileCreationTime']['value'] = Utils::formatTime();
    }

    /**
     * Set control values.
     */
    protected function setControl()
    {
        $this->control = array_merge(Arr::get($this->options, 'control', []), FileDefinition::$controls);
    }

    /**
     * Set object attributes.
     */
    protected function setValues()
    {
        // This is done to make sure we have a 9-digit routing number
        if (array_key_exists('immediateDestination', $this->options)) {
            $this->header['immediateDestination'] = Utils::computeCheckDigit($this->options['immediateDestination']);
        }

        $this->batchSequenceNumber = (int) Arr::get($this->options, 'batchSequenceNumber', 0);
    }
}
