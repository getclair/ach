<?php

namespace Clair\Ach;

use Clair\Ach\Definitions\Addenda as AddendaDefinition;
use Clair\Ach\Definitions\Batch as BatchDefinition;
use Clair\Ach\Definitions\Entry as EntryDefinition;
use Clair\Ach\Definitions\File as FileDefinition;
use Clair\Ach\Support\Utils;

class Parser
{
    protected string $contents;

    /**
     * Parser constructor.
     * @param string $contents
     */
    public function __construct(string $contents)
    {
        $this->contents = trim($contents);
    }

    /**
     * @param $name
     * @param $arguments
     */
    public static function __callStatic($name, $arguments)
    {
        if (method_exists(static::class, $name)) {
            forward_static_call_array($name, $arguments);
        }
    }

    /**
     * Parse contents into a File.
     *
     * @return File
     * @throws \Exception
     */
    public function parse(): File
    {
        if (strlen($this->contents) === 0) {
            throw new \Exception('Contents are empty.');
        }

        $fileOptions = $this->prepareOptions();

        $file = new File($fileOptions->header);

        foreach ($fileOptions->batches as $batchData) {
            $batch = new Batch($batchData['header']);

            foreach ($batchData['entry'] as $entry) {
                $batch->addEntry($entry);
            }

            $file->addBatch($batch);
        }

        return $file;
    }

    /**
     * Produce lines from the contents.
     *
     * @return array
     */
    protected function lines(): array
    {
        return explode("\n", $this->contents);
    }

    /**
     * Define options for NACHA file.
     *
     * @return FileOptions
     * @throws Exceptions\AchValidationException
     */
    protected function prepareOptions(): FileOptions
    {
        $fileOptions = new FileOptions();
        $batchIndex = 0;

        foreach ($this->lines() as $line) {
            switch ((int) $line[0]) {

                // Set file header
                case 1:
                    $fileOptions->setHeader(Utils::parseLine($line, FileDefinition::$header));
                    break;

                // Set file control
                case 9:
                    $fileOptions->setControl(Utils::parseLine($line, FileDefinition::$control));
                    break;

                // Setup batch
                case 5:
                    $fileOptions->updateBatch($batchIndex, [
                        'header' => Utils::parseLine($line, BatchDefinition::$header),
                        'entry' => [],
                    ]);

                    break;

                // Update control on batch and move to the next one.
                case 8:
                    $fileOptions->updateBatch($batchIndex, [
                        'control' => Utils::parseLine($line, BatchDefinition::$control),
                    ]);

                    $batchIndex++;
                    break;

                // Add entry on batch
                case 6:
                    $batch = $fileOptions->batches[$batchIndex];
                    $batch['entry'][] = new Entry(Utils::parseLine($line, EntryDefinition::$fields));
                    $fileOptions->updateBatch($batchIndex, $batch);
                    break;

                // Add addenda
                case 7:
                    $batch = $fileOptions->batches[$batchIndex];
                    $index = count($batch['entry']) - 1;
                    $batch['entry'][$index]->addAddenda(new Addenda(Utils::parseLine($line, AddendaDefinition::$fields)));
                    $fileOptions->updateBatch($batchIndex, $batch);

                    break;

                default:
                    break;
            }
        }

        if (! $fileOptions->header || ! $fileOptions->control) {
            throw new \ParseError('File records parse error');
        }

        if (count($fileOptions->batches) === 0) {
            throw new \ParseError('No batches found');
        }

        return $fileOptions;
    }
}
