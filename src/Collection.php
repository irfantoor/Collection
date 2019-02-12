<?php
/**
 * Collection
 * php version 7.0
 *
 * @category  Collection
 * @package   IrfanTOOR_Collection
 * @author    Irfan TOOR <email@irfantoor.com>
 * @copyright 2017 Irfan TOOR
 * @license   https://github.com/irfantoor/collection/blob/master/LICENSE (MIT)
 * @link      https://github.com/irfantoor/collection/blob/master/src/Collection.php
 */

namespace IrfanTOOR;

use ArrayIterator;
use Exception;
use IrfanTOOR\Collection\Constants;

/**
 * Collection implementing ArrayAccess, Countable and IteratorAggregate
 *
 * @category  Collection
 * @package   IrfanTOOR_Collection
 * @author    Irfan TOOR <email@irfantoor.com>
 * @copyright 2017 Irfan TOOR
 * @license   https://github.com/irfantoor/collection/blob/master/LICENSE (MIT)
 * @link      https://github.com/irfantoor/collection/blob/master/src/Collection.php
 */
class Collection implements \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * The source data
     *
     * @var array
     */
    protected $data = [];

    // =========================================================================
    // Collection
    // =========================================================================

    /**
     * Constructs the collection
     *
     * @param Array $init array of key, value pair to initialize our
     *                    collection with e.g. ['hello' => 'world']
     */
    public function __construct($init = [])
    {
        $this->data = [];
        $this->set($init);
    }

    /**
     * Returns the current version of the package
     *
     * @return string git version of this package
     */
    public function version()
    {
        return Constants::VERSION;
    }    

    /**
     * Sets an $identifier and its Value pair
     *
     * @param String $id    identifier or array of id, value pairs
     * @param Mixed  $value value of identifier or null if the parameter
     *                      id is an array
     *
     * @return boolval true If successful in setting, false otherwise
     */
    public function set($id, $value = null)
    {
        if (is_array($id)) {
            foreach ($id as $k => $v) {
                $this->set($k, $v);
            }
        } elseif (is_string($id)) {
            return $this->setItem($id, $value);
        } else {
            return false;
        }
    }

    /**
     * Sets an $identifier and its Value pair
     * It is defined separately to facilitate extending the collection
     *
     * @param String $id    identifier
     * @param Mixed  $value value of identifier
     *
     * @return boolval true If successful in setting, false otherwise
     */
    public function setItem($id, $value)
    {
        try {
            if (strpos($id, '.') !== false) {
                eval(
                    '$this->data' .
                    "['" .
                    str_replace('.', "']['", $id) .
                    "']" .
                    '= $value;'
                );
            } else {
                $this->data[$id] = $value;
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Returns true if the collection can return an entry for the given
     * identifier, returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw
     * an exception.
     * It does however mean that `get($id)` will not throw a
     * `NotFoundExceptionInterface`.
     *
     * @param String $id Identifier of the entry to look for.
     *
     * @return boolval true if found, false otherwise
     */
    public function has($id)
    {
        if (strpos($id, '.') !== false) {
            $k = '$this->data' . "['" . str_replace('.', "']['", $id) . "']";
            eval('$has = isset(' . $k . ');');
            return $has;
        } else {
            return (is_string($id)) ? array_key_exists($id, $this->data) : false;
        }
    }

    /**
     * Finds an entry of the collection by its identifier and returns it.
     *
     * @param String $id      Identifier of the entry to look for.
     * @param Mixed  $default A default value.
     *
     * @return Mixed Entry.
     */
    public function get($id, $default = null)
    {
        if (strpos($id, '.') !== false) {
            $k = '$this->data' . "['" . str_replace('.', "']['", $id) . "']";
            eval('$has = isset(' . $k . ');');
            if ($has) {
                eval('$value = ' . $k . ';');
                return $value;
            } else {
                return $default;
            }
        } else {
            return $this->has($id) ? $this->data[$id] : $default;
        }
    }

    /**
     * Removes the value from identified by an identifier from the colection
     *
     * @param String $id identifier
     *
     * @return boolval true if successful in removing, false otherwise
     */
    public function remove($id)
    {
        if (strpos($id, '.') !== false) {
            $k = '$this->data' . "['" . str_replace('.', "']['", $id) . "']";
            eval('$has = isset(' . $k . ');');
            if ($has) {
                eval('unset(' . $k . ');');
                return true;
            } else {
                return false;
            }
        } elseif ($this->has($id)) {
            unset($this->data[$id]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns all the items in the collection as raw array
     *
     * @return Array The collection's raw data
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Get collection keys
     *
     * @return Array The collection's raw data keys
     */
    public function keys()
    {
        return array_keys($this->data);
    }

    // =========================================================================
    // ArrayAccess interface
    // =========================================================================

    /**
     * Set collection item
     *
     * @param String $id    The data key
     * @param Mixed  $value The data value
     *
     * @return boolval true if found, false otherwise
     */
    public function offsetSet($id, $value)
    {
        return $this->set($id, $value);
    }

    /**
     * Does this collection have a given key?
     *
     * @param String $id The data key
     *
     * @return boolval true if found, false otherwise
     */
    public function offsetExists($id)
    {
        return $this->has($id);
    }

    /**
     * Get collection item for key
     *
     * @param String $id The data key
     *
     * @return Mixed The key's value, or the default value
     */
    public function offsetGet($id)
    {
        return $this->get($id, null);
    }

    /**
     * Remove item from collection
     *
     * @param String $id The data key
     *
     * @return boolval true if successful, false otherwise
     */
    public function offsetUnset($id)
    {
        return $this->remove($id);
    }

    // =========================================================================
    // Countable interface
    // =========================================================================

    /**
     * Get number of items in collection
     *
     * @return Int
     */
    public function count()
    {
        return count($this->data);
    }

    // =========================================================================
    // IteratorAggregate interface
    // =========================================================================

    /**
     * Get collection iterator
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }
}
