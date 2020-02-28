<?php

namespace IrfanTOOR;

class SingleLevelCollection extends Collection
{
    function set($id, $value)
    {
        if ($this->locked) return false;

        try {
            $this->data[$id] = $value;
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    function has($id)
    {
        return (is_string($id)) ? array_key_exists($id, $this->data) : false;
    }

    function get($id, $default = null)
    {
        return $this->has($id) ? $this->data[$id] : $default;
    }

    function remove($id)
    {
        if ($this->locked) return false;

        if ($this->has($id)) {
            unset($this->data[$id]);
            return true;
        }

        return false;
    }
}
