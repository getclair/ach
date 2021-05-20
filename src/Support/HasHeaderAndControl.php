<?php

namespace Clair\Ach\Support;

trait HasHeaderAndControl
{
    /**
     * Get a header value.
     *
     * @param $key
     * @return mixed
     */
    public function getHeaderValue($key)
    {
        if (array_key_exists($key, $this->header)) {
            return $this->header[$key]['value'];
        }
    }

    /**
     * Get a control value.
     *
     * @param $key
     * @return mixed
     */
    public function getControlValue($key)
    {
        if (array_key_exists($key, $this->control)) {
            return $this->control[$key]['value'];
        }
    }

    /**
     * Set a header value.
     *
     * @param $key
     * @param $value
     */
    public function setHeaderValue($key, $value)
    {
        if (array_key_exists($key, $this->header)) {
            $this->header[$key]['value'] = $value;
        }
    }

    /**
     * Set a control value.
     *
     * @param $key
     * @param $value
     */
    public function setControlValue($key, $value)
    {
        if (array_key_exists($key, $this->control)) {
            $this->control[$key]['value'] = $value;
        }
    }

    /**
     * Get a header or control value, whichever occurs first.
     *
     * @param $key
     * @return mixed
     */
    public function getFieldValue($key)
    {
        if ($this->getHeaderValue($key)) {
            return $this->getHeaderValue($key);
        }

        if ($this->getControlValue($key)) {
            return $this->getControlValue($key);
        }
    }

    /**
     * Set a header and/or control value.
     *
     * @param $key
     * @param $value
     */
    public function setFieldValue($key, $value)
    {
        if ($this->getHeaderValue($key)) {
            $this->setHeaderValue($key, $value);
        }

        if ($this->getControlValue($key)) {
            $this->setControlValue($key, $value);
        }
    }

    /**
     * Increment a control value.
     *
     * @param $key
     */
    public function incrementControlValue($key)
    {
        $value = $this->getControlValue($key);

        $this->setControlValue($key, $value++);
    }
}
