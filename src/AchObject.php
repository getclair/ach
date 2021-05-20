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
    protected array $overrides = [];

    /**
     * Boot the object by setting configuration and values.
     */
    protected function boot()
    {
        if (method_exists(static::class, 'setFields')) {
            $this->setFields();
        }

        if (method_exists(static::class, 'setHeader')) {
            $this->setHeader();
        }

        if (method_exists(static::class, 'setControl')) {
            $this->setControl();
        }

        $this->setOverrides();
        $this->setValues();
    }

    /**
     * Set any overrides.
     * @param array $groups
     */
    protected function setOverrides()
    {
        foreach ($this->overrides as $group => $keys) {
            foreach ($keys as $key) {
                if (array_key_exists($key, $this->options)) {
                    $this->setValue($group, $key, $this->options[$key]);
                }
            }
        }
    }
}
