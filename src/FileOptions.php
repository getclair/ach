<?php

namespace Clair\Ach;

use Illuminate\Contracts\Support\Arrayable;

class FileOptions implements Arrayable
{
    /**
     * Header raw data.
     *
     * @var
     */
    public $header;

    /**
     * Control raw data.
     *
     * @var
     */
    public $control;

    /**
     * Batches raw data.
     *
     * @var array
     */
    public array $batches = [];

    /**
     * @param mixed $header
     */
    public function setHeader($header): void
    {
        $this->header = $header;
    }

    /**
     * @param mixed $control
     */
    public function setControl($control): void
    {
        $this->control = $control;
    }

    /**
     * @param $index
     * @param array $values
     */
    public function updateBatch($index, array $values): void
    {
        if (! array_key_exists($index, $this->batches)) {
            $this->batches[$index] = [];
        }

        $this->batches[$index] = array_merge($this->batches[$index], $values);
    }

    /**
     * Return options as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'header' => $this->header,
            'control' => $this->control,
            'batches' => $this->batches,
        ];
    }
}
