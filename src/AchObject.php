<?php

namespace Clair\Ach;

use Clair\Ach\Definitions\FieldTypes;
use Clair\Ach\Support\HandlesValues;

abstract class AchObject
{
    use HandlesValues;

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

    /**
     * Cast a value into a PHP type from ACH type.
     *
     * @param $value
     * @param $type
     * @return mixed
     */
    public function cast($value, $type)
    {
        settype($value, $this->getCast($type));

        return $value;
    }

    /**
     * Return the PHP cast type.
     *
     * @param $type
     * @return string
     */
    public function getCast($type): string
    {
        return $type === FieldTypes::TYPE_NUMERIC ? 'int' : 'string';
    }
}
