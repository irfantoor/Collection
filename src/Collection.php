<?php

/**
 * IrfanTOOR\Collection
 * php version 7.3
 *
 * @author    Irfan TOOR <email@irfantoor.com>
 * @copyright 2021 Irfan TOOR
 */

namespace IrfanTOOR;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Throwable;

/**
 * Collection - An enhansed array, in which elements can be accessed using 
 * the dot to sparate the level:
 *   e.g. $c = new Collection($init); // where $init is a key, value array 
 *   $element = $c->get('hello.world'); # to access $c['hello']['world'] etc.
 * Note: The adapters are no longer supported, and collections are considered
 * to be in memory. Cache, Database or Filesystem can be used to make a
 * collection persistant.
 */
class Collection 
    implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    const NAME        = "Collection";
    const DESCRIPTION = "A simple collection, with dot notation";
    const VERSION     = "3.0";

    /** @var array -- to keep track of [$key => $value] pairs */
    protected $data = [];

    /** @var bool -- Semaphore to track the lock status */
    private $is_locked = false;

    /**
     * Collection constructor
     *
     * @param array $init Array containing an associative array of data
     */
    public function __construct(?array $init = null)
    {
        if ($init)
            $this->setMultiple($init);
    }

    /**
     * Magic methods -- to protect against accidental bad usage
     */
    public function __call($method, $args) {}
    public function __set($key, $value) {}
    public function __get($key) {return null;}

    /**
     * Locks the collection, once locked the collection can not be unlocked
     */
    public function lock()
    {
        $this->is_locked = true;
    }

    /**
     * Sets the multiple values
     *
     * @param array $data Associative array of key, value pairs
     * @return bool Result of the operation
     */
    public function setMultiple(array $data): bool
    {
        if ($this->is_locked)
            return false;

        $final_result = true;

        foreach ($data as $key => $value) {
            $result = $this->set($key, $value);
            $final_result = $final_result && $result;
        }

        return $final_result;
    }

    /**
     * sets a key value pair
     *
     * @param string $key   Key of the entity to be stored
     * @param mixed  $value Value of the entity to be stored
     * @return bool Result of the operation
     */
    public function set(string $key, $value): bool
    {
        if ($this->is_locked)
            return false;

        try {
            if ($pos = strrpos($key, '.')) {
                $v = $this->get(substr($key, 0, $pos)) ?? null;

                if (!(is_array($v) || is_null($v)))
                    return false;
            }

            eval(
                '$' . "this->data['" . str_replace(".", "']['", $key) . "']" .
                ' = ' .
                '$value;'
            );

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Verifies if a key is present in the collection
     *
     * @param string $key Key of the entity to be verified
     * @return bool True if present, false otherwise
     */
    public function has(string $key): bool
    {
        if (strpos($key, '.') === false) {
            return array_key_exists($key, $this->data);
        }

        $d = &$this->data;
        $kk = explode('.', $key);

        $k = array_shift($kk);

        while ($k !== null) {
            if (!is_array($d))
                return false;

            if (array_key_exists($k, $d)) {
                $d = &$d[$k];
            } else {
                return false;
            }

            $k = array_shift($kk);
        }

        return true;
    }

    /**
     * Retrieves the $value of the entity stored against $key, or $default,
     * if the $key is not present
     *
     * @param string $key     Key of the entity to be retrieved
     * @param mixed  $default Value to be returned if the key is not found
     * @return mixed The value of the entity stored against key
     */
    public function get(string $key, $default = null)
    {
        if (!$this->has($key))
            return $default;

        eval(
            '$value' .
            ' = ' .
            '$this->data' . "['" . str_replace('.', "']['", $key) . "'];"
        );

        return $value;
    }

    /**
     * Removes an entity
     *
     * @param string $key Key of the entity to be removed
     * @return bool True if the operation was successful, False otherwise
     */
    public function remove(string $key): bool
    {
        if ($this->is_locked)
            return false;

        if (!$this->has($key))
            return false;

        eval(
            'unset($this->data' .
            "['" . str_replace('.', "']['", $key) .
            "']);"
        );

        return true;
    }

    /**
     * Set collection item using array's notation
     * e.g. $c['hello'] = 'world!';
     *
     * @param string $key   The data key
     * @param mixed  $value The data value
     * @return bool Result of the operation, true if successful, false otherwise
     */
    public function offsetSet($key, $value): bool
    {
        return $this->set($key, $value);
    }

    /**
     * Verifies if the collection has an element using array's notation
     * $planet = $c['water'] ?? 'unknown world!';
     *
     * @param string $key Key of the entity to verify
     * @return bool True if the entity is present, false otherwise
     */
    public function offsetExists($key): bool
    {
        return $this->has($key);
    }

    /**
     * Retrieves an element from the collection using array's notation
     * e.g. $planet = $c['hello'];
     *
     * @param string $key Key of the entity to retrieve
     * @return null|mixed Null if not found or the element
     */
    public function offsetGet($key)
    {
        return $this->get($key, null);
    }
    
    /**
     * Removes an element from the collection using array's notation
     * e.g unset($c['hello']]);
     *
     * @param string $key Key of the element to remove
     * @return bool True if succefully removed, false otherwise
     */
    public function offsetUnset($key): bool
    {
        return $this->remove($key);
    }

    /**
     * Returns the collection as an array
     *
     * @return array
     */
    function toArray(): array
    {
        return $this->data;
    }

    /**
     * Retrieves the collection iterator
     * e.g. foreach($c as $key => $value) { ... }
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->toArray());
    }

    /**
     * Returns the keys of the collection as an array
     * 
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this->data);
    }

    /**
     * Retrieves the count of elements in the collection
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Class can be json_encoded
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Returns a sub collection, with the elements passing the callback test
     * e.g. $callback = function ($k, $v) { return is_string($k) && is_int($v); }
     *
     * @param callback|object|closure $callback
     * @return Collection
     */
    public function filter($callback): Collection
    {
        $collection = new self();

        foreach ($this->data as $key => $value)
            if ($callback($key, $value))
                $collection->set($key, $value);

        return $collection;
    }

    /**
     * Returns the collection, with the values callculated by $callback 
     * e.g. $callback = function($k, $v) { return $v * $v; }
     *
     * @param callback|object|closure $callback
     * @return Collection
     */
    public function map($callback): Collection
    {
        $collection = new self();

        foreach ($this->data as $key => $value)
            $collection->set($key, $callback($key, $value));

        return $collection;
    }

    /**
     * Rduces the collection to a result, by applying the callback recursively
     * to previous result and the next element
     * e.g. $callback = function($k, $v, $carry) = {
     *                      return $carry + (is_int($v) ? $v : 0};
     *                  }
     *
     * @param callback|object|closure $callback
     * @param mixed                   $init
     * @return Collection
     */
    public function reduce($callback, $init = null)
    {
        $carry = $init;

        foreach ($this->data as $key => $value)
            $carry = $callback($key, $value, $carry);

        return $carry;
    }
}
