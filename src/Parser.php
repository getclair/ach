<?php

namespace Clair\Ach;

use Clair\Ach\Dictionaries\Addenda as AddendaDictionary;
use Clair\Ach\Dictionaries\Batch as BatchDictionary;
use Clair\Ach\Dictionaries\File as FileDictionary;
use Clair\Ach\Support\Utils;

class Parser
{
    protected string $contents;

    protected bool $hasAddenda = false;

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
    protected function parse(): File
    {
        if (strlen($this->contents) === 0) {
            throw new \Exception('Contents are empty.');
        }

        $fileOptions = $this->prepareOptions();

        $file = new File($fileOptions, $this->hasAddenda);

        foreach ($fileOptions->batches as $item) {
            $batch = new Batch($item['header']);

            foreach ($item['entry'] as $entry) {
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
     */
    protected function prepareOptions(): FileOptions
    {
        $fileOptions = new FileOptions();
        $batchIndex = 0;

        foreach ($this->lines() as $line) {
            switch ((int) $line[0]) {
                case 1:
                    $fileOptions->setHeader(Utils::parseLine($line, FileDictionary::$headers));
                    break;
                case 9:
                    $fileOptions->setControl(Utils::parseLine($line, FileDictionary::$controls));
                    break;
                case 5:
                    $fileOptions->addBatch([
                        'header' => Utils::parseLine($line, BatchDictionary::$headers),
                        'entry' => [],
                        'addenda' => [],
                    ]);
                    break;
                case 8:
                    $fileOptions->updateBatch($batchIndex, [
                        'control' => Utils::parseLine($line, BatchDictionary::$controls),
                    ]);

                    $batchIndex++;
                    break;
                case 6:
                    $batch = $fileOptions->batches[$batchIndex];
                    $batch['entry'][] = Utils::parseLine($line, BatchDictionary::$controls);
                    $fileOptions->updateBatch($batchIndex, $batch);
                    break;
                case 7:
                    $batch = $fileOptions->batches[$batchIndex];
                    $index = count($batch['entry']) - 1;
                    $batch['entry'][$index]->addAddenda(new Addenda(Utils::parseLine($line, AddendaDictionary::$fields)));
                    $fileOptions->updateBatch($batchIndex, $batch);

                    $this->hasAddenda = true;

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
