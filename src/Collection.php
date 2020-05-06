<?php
/**
 * IrfanTOOR\Collection
 * php version 7.3
 *
 * @package   IrfanTOOR\Collection
 * @author    Irfan TOOR <email@irfantoor.com>
 * @copyright 2020 Irfan TOOR
 */
namespace IrfanTOOR;

use ArrayAccess;
use ArrayIterator;
use Countable;
use Exception;

use IrfanTOOR\Collection\Adapter\{
    AdapterInterface,
    ExtendedAdapter
};

use IteratorAggregate;
use Throwable;

/**
 * Collection - It can be considered as an enhansed array, in which
 * you can access the elements using dotted notation e.g:
 * $c = new Collection($init);
 * $element = $c->get('hello.world'); # to access $c['hello']['world'] etc.
 */
class Collection implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * Collection::VERSION
     *
     * @var const
     */
    const VERSION = "2.0"; // @@VERSION

    /**
     * Adapter
     *
     * @var CollectionAdapterInterface
     */
    protected $adapter;

    /**
     * Collection constructor
     *
     * @param array                 $init    Associative are to initialize
     * @param null|AdapterInterface $adapter CollectionAdapter to use with this 
     *                                       collection. default is ExtendedAdapter
     */
    public function __construct(array $init = [], ?AdapterInterface $adapter = null)
    {
        if (!$adapter) {
            $this->setAdapter(new ExtendedAdapter($init));
        } else {
            $this->setAdapter($adapter);
            $this->setMultiple($init);
        }
    }

    /**
     * Calls the adapter for the methods
     *
     * @param string $method Adapter method to be called
     * @param array  $args   Array of parameters to be passed
     * 
     * @return mixed
     */
    public function __call(string $method, array $args)
    {
        return call_user_func_array([$this->adapter, $method], $args);
    }

    /**
     * Sets the Adapter
     *
     * @param mixed $adapter Collection adapter to use (string | AdapterInterface)
     *
     * @throws Exception If the provided adapter is neither the classname,
     *                   nor an object implementing AdapterInterface, or 
     *                   if the class name can not be initialized
     *
     * @return void
     */
    public function setAdapter($adapter)
    {
        if (is_string($adapter)) {
            try {
                $adapter = new $adapter();
            } catch (Throwable $e) {
                throw new Exception($e->getMessage());
            }
        } elseif (!is_object($adapter)) {
            throw new Exception("Adapter can either be classname or an object");
        }

        if (!$adapter instanceof AdapterInterface) {
            throw new Exception(
                "Adapter must implement the " . AdapterInterface::class
            );
        }

        $this->adapter = $adapter;
    }

    /**
     * Returns the Adapter
     *
     * @return AdapterInterface
     */
    public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }

    /**
     * Set collection item using array's notation
     * e.g. $c['hello'] = 'world!';
     *
     * @param string $key   The data key
     * @param mixed  $value The data value
     *
     * @return bool Result of the operation, true if successful, false otherwise
     */
    public function offsetSet($key, $value)
    {
        return $this->adapter->set($key, $value);
    }

    /**
     * Verifies if the collection has an element using array's notation
     * $planet = $c['water'] ?? 'unknown world!';
     *
     * @param string $key Key to look for
     *
     * @return bool Result of the operation, true if successful, false otherwise
     */
    public function offsetExists($key)
    {
        return $this->adapter->has($key);
    }

    /**
     * Retrieves an element from the collection using array's notation
     * e.g. $planet = $c['hello'];
     *
     * @param string $key Key of the element
     *
     * @return null|mixed Null if not found or the element
     */
    public function offsetGet($key)
    {
        return $this->adapter->get($key, null);
    }

    /**
     * Removes an element from the collection using array's notation
     * e.g unset($c['hello']]);
     *
     * @param string $key Key of the element to look for
     *
     * @return bool Result of the operation
     */
    public function offsetUnset($key)
    {
        return $this->adapter->remove($key);
    }

    /**
     * Retrieves the collection iterator
     * e.g. foreach($c as $key => $value) { ... }
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->adapter->toArray());
    }

    /**
     * Retrieves the count of elements in the collection
     *
     * @return int
     */
    public function count(): int
    {
        return $this->adapter->count();
    }
    
    /**
     * Returns the collection of the elements which return true
     *
     * NOTE: $callback function must uses parameters in the following order
     * for the provided callback function:
     *       param_1 $value Value of the current element
     *       param_2 $key   Key of the current element
     *    
     * example:
     *       $callback = function ($value, $key) {
     *           return is_int($value);
     *       };
     *
     *       $collection_of_int_values = $c->filter($callback);
     *
     * @param mixed $callback Callback (closure|object|function)
     *
     * @return Collection
     */
    public function filter($callback): Collection
    {
        $result = array_filter(
            $this->adapter->toArray(), $callback, ARRAY_FILTER_USE_BOTH
        );

        return new self($result);
    }

    /**
     * Returns a collection with the callback applied to the element values
     * of this collection:
     *
     * Example:
     *       $callback = functin ($value) {
     *           return $value * $value;
     *       };
     * 
     *       $squares_of_values = $c->map($callback);
     *
     * @param mixed $callback Callback function (closure|object|function)
     *
     * @return Collection
     */
    public function map($callback): Collection
    {
        $result = array_map($callback, $this->adapter->toArray());
        return new self($result);
    }

    /**
     * Reduces the array to a result, by applying the function to all of its elements
     *
     * NOTE: $callback function must uses parameters in the following order:
     *       param_1 $carry Result of callback operation on the previous element
     *       param_2 $value Value of the current element
     *       param_3 $key   Key of the current element
     *
     * Example:
     *       $callback = functin ($carry, $value, $key) {
     *           return is_int($value) ? $carry + $value : $carry;
     *       };
     *
     *       $sum_of_values = $c->reduce($callback);
     *
     * @param closure|object|function $callback Callback function
     * @param mixed                   $init     Initial value
     *
     * @return mixed
     */
    public function reduce($callback, $init = null)
    {
        $carry = $init;

        foreach ($this->adapter->toArray() as $key => $value) {
            $carry = $callback($carry, $value, $key);
        }

        return $carry;
    }
}
