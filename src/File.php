<?php

namespace Clair\Ach;

use Clair\Ach\Definitions\File as FileDefinition;
use Clair\Ach\Support\Utils;
use Illuminate\Support\Arr;
use Spatie\Async\Pool;

class File extends AchObject
{
    public const LINE_WIDTH = 94;

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
    protected array $overrides = [
        'header' => ['immediateDestination', 'immediateOrigin', 'fileCreationDate', 'fileCreationTime', 'fileIdModifier', 'immediateDestinationName', 'immediateOriginName', 'referenceCode'],
        'control' => ['immediateDestination', 'immediateOrigin', 'fileCreationDate', 'fileCreationTime', 'fileIdModifier', 'immediateDestinationName', 'immediateOriginName', 'referenceCode'],
    ];

    /**
     * File constructor.
     * @param array $options
     * @param false $autoValidate
     */
    public function __construct(FileOptions $options, $autoValidate = true)
    {
        $this->options = $options->header;

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
        $this->batchSequenceNumber++;

        $batch->setHeaderValue('batchNumber', $this->batchSequenceNumber);
        $batch->setControlValue('batchNumber', $this->batchSequenceNumber);

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
        $results = [];

        for ($i = 0; $i < $rows; $i++) {
            $results[] = str_pad('', self::LINE_WIDTH, '9');
        }

        return implode(Utils::NEWLINE, $results);
    }

    /**
     * Generate file batches.
     *
     * @return array
     */
    public function generateBatches(): array
    {
        $results = [];
        $rows = 2;
        $batchCount = 0;

        $entryHash = 0;
        $addendaCount = 0;

        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($this->batches as $batch) {
            $totalDebit += $batch->getControlValue('totalDebit');
            $totalCredit += $batch->getControlValue('totalCredit');

            foreach ($batch->getEntries() as $entry) {
                $traceNumber = $entry->getFieldValue('traceNumber')
                    ?: substr($this->getHeaderValue('immediateOrigin'), 0, 8).str_pad($addendaCount, 7, '0', STR_PAD_LEFT);

                $entry->setFieldValue('traceNumber', $traceNumber);

                $entryHash += (int) $entry->getFieldValue('receivingDFI');

                $addendaCount++;
                $rows++;
            }

            if (count($batch->getEntries()) > 0) {
                $batchCount++;

                $rows = $rows + 2;

                $results[] = $batch->generateString();
            }
        }

        $this->setControlValue('batchCount', $batchCount);
        $this->setControlValue('totalDebit', $totalDebit);
        $this->setControlValue('totalCredit', $totalCredit);
        $this->setControlValue('addendaCount', $addendaCount);
        $this->setControlValue('blockCount', Utils::getNextMultiple($rows, 10) / 10);
        $this->setControlValue('entryHash', substr($entryHash, -10));

        return [implode(Utils::NEWLINE, $results), $rows];
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
        [$batchString, $rows] = $this->generateBatches();
        $controlString = $this->generateControl();
        $paddedString = $this->generatePaddedRows(Utils::getNextMultipleDiff($rows, 10));

        return implode(Utils::NEWLINE, array_map('trim', [$headerString, $batchString, $controlString, $paddedString]));
    }

    /**
     * Return file options.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Set header values.
     */
    protected function setHeader()
    {
        $this->header = array_merge(Arr::get($this->options, 'header', []), FileDefinition::$header);

        $this->setHeaderValue('fileCreationDate', Utils::formatDate());
        $this->setHeaderValue('fileCreationTime', Utils::formatTime());
    }

    /**
     * Set control values.
     */
    protected function setControl()
    {
        $this->control = array_merge(Arr::get($this->options, 'control', []), FileDefinition::$control);
    }

    /**
     * Set object attributes.
     */
    protected function setValues()
    {
        foreach ($this->header as $key => $attributes) {
            if (array_key_exists($key, $this->options)) {
                $this->setHeaderValue($key, $this->options[$key]);
            }
        }

        // This is done to make sure we have a 9-digit routing number
        if (array_key_exists('immediateDestination', $this->options) && strlen($this->options['immediateDestination']) === 8) {
            $this->setHeaderValue('immediateDestination', Utils::addCheckDigit($this->options['immediateDestination']));
        }

        $this->batchSequenceNumber = (int) Arr::get($this->options, 'batchSequenceNumber', 0);
    }
}
