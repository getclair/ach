<?php

namespace Clair\Ach;

use Illuminate\Contracts\Support\Arrayable;

class FileOptions implements Arrayable
{
    public $header;

    public $control;

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
     * @param mixed $batch
     */
    public function updateBatch($index, array $values): void
    {
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
