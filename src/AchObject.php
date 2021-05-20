<?php

namespace Clair\Ach;

use Clair\Ach\Support\HandlesValues;

abstract class AchObject
{
    use HandlesValues;

    /**
     * @var array
     */
    protected array $options;

    /**
     * @var array
     */
    protected array $highLevelOverrides = [];

    /**
     * Boot the object.
     *
     * @return mixed
     */
    abstract protected function boot();

    /**
     * Set any overrides.
     */
    protected function setOverrides()
    {
        foreach ($this->highLevelOverrides as $key) {
            if (array_key_exists($key, $this->options)) {
                $this->setFieldValue($key, $this->options[$key]);
            }
        }
    }
}
