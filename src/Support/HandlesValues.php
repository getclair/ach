<?php

namespace Clair\Ach\Support;

use Clair\Ach\Definitions\FieldTypes;

trait HandlesValues
{
    /**
     * Get a group value.
     *
     * @param $group
     * @param $key
     * @return mixed
     */
    public function getValue($group, $key)
    {
        if (isset($this->$group) && array_key_exists($key, $this->$group)) {
            return $this->$group[$key]['value'];
        }
    }

    /**
     * Set a group value.
     *
     * @param $group
     * @param $key
     * @param $value
     */
    public function setValue($group, $key, $value = null)
    {
        if (isset($this->$group) && array_key_exists($key, $this->$group)) {
            $cast = $this->{$group}[$key]['type'] ?? FieldTypes::TYPE_ALPHANUMERIC;
            $this->$group[$key]['value'] = $this->cast($value, $cast);
        }
    }

    /**
     * Get a header value.
     *
     * @param $key
     * @return mixed
     */
    public function getHeaderValue($key)
    {
        return $this->getValue('header', $key);
    }

    /**
     * Set a header value.
     *
     * @param $key
     * @param $value
     */
    public function setHeaderValue($key, $value)
    {
        $this->setValue('header', $key, $value);
    }

    /**
     * Get a control value.
     *
     * @param $key
     * @return mixed
     */
    public function getControlValue($key)
    {
        return $this->getValue('control', $key);
    }

    /**
     * Set a control value.
     *
     * @param $key
     * @param $value
     */
    public function setControlValue($key, $value)
    {
        $this->setValue('control', $key, $value);
    }

    /**
     * Get a header or control value, whichever occurs first.
     *
     * @param $key
     * @return mixed
     */
    public function getFieldValue($key)
    {
        return $this->getValue('fields', $key);
    }

    /**
     * Set a header and/or control value.
     *
     * @param $key
     * @param $value
     */
    public function setFieldValue($key, $value)
    {
        $this->setValue('fields', $key, $value);
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
