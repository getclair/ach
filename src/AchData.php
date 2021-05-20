<?php

namespace Clair\Ach;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class AchData implements Arrayable, Jsonable
{
    protected File $file;

    /**
     * AchData constructor.
     * @param File $file
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }

    public function toArray()
    {
        return $this->file->getOptions();
    }

    /**
     * @param int $options
     * @return false|string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }
}
