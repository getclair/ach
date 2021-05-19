<?php

namespace Clair\Ach\Support;

trait HasHeaderAndControl
{
    public function getHeaderValue($key)
    {
        if (array_key_exists($key, $this->header)) {
            return $this->header[$key]['value'];
        }
    }

    public function getControlValue($key)
    {
        if (array_key_exists($key, $this->control)) {
            return $this->control[$key]['value'];
        }
    }

    public function setHeaderValue($key, $value)
    {
        if (array_key_exists($key, $this->header)) {
            $this->header[$key]['value'] = $value;
        }
    }

    public function setControlValue($key, $value)
    {
        if (array_key_exists($key, $this->control)) {
            $this->control[$key]['value'] = $value;
        }
    }

    public function getFieldValue($key)
    {
        return $this->getHeaderValue($key) ?? $this->getControlValue($key);
    }

    public function setFieldValue($key, $value)
    {
        $this->setHeaderValue($key, $value);
        $this->setControlValue($key, $value);
    }

    public function incrementControlValue($key)
    {
        $value = $this->getControlValue($key);

        $this->setControlValue($key, $value++);
    }
}
