<?php

namespace Clair\Ach;

abstract class AchObject
{
    /**
     * @var array
     */
    protected array $fields = [];

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
     * Get a field value.
     *
     * @param $key
     * @return mixed|string
     */
    public function getFieldValue($key)
    {
        if (array_key_exists($key, $this->fields)) {
            return $this->fields[$key]['value'];
        }

        return '';
    }

    /**
     * Set a field value.
     *
     * @param $key
     * @param $value
     */
    public function setFieldValue($key, $value)
    {
        if (array_key_exists($key, $this->fields)) {
            $this->fields[$key]['value'] = $value;
        }
    }

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
