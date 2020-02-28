<?php
/**
 * Collection
 * php version 7.3
 *
 * @category  Collection
 * @package   IrfanTOOR_Collection
 * @author    Irfan TOOR <email@irfantoor.com>
 * @copyright 2020 Irfan TOOR
 * @license   https://github.com/irfantoor/collection/blob/master/LICENSE (MIT)
 * @link      https://github.com/irfantoor/collection/blob/master/src/Collection.php
 */

namespace IrfanTOOR;

use ArrayIterator;
use Exception;

/**
 * Collection implementing ArrayAccess, Countable and IteratorAggregate
 *
 * @category  Collection
 * @package   IrfanTOOR_Collection
 * @author    Irfan TOOR <email@irfantoor.com>
 * @copyright 2020 Irfan TOOR
 * @license   https://github.com/irfantoor/collection/blob/master/LICENSE (MIT)
 * @link      https://github.com/irfantoor/collection/blob/master/src/Collection.php
 */
class Collection implements \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * Collection Version
     *
     * @var const
     */    
    const VERSION = "1.6"; // @@VERSION

    /**
     * The source data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Semaphore to indicate that the data is locked
     *
     * @var bool
     */
    protected $locked = false;

    // =========================================================================
    // Collection
    // =========================================================================

    /**
     * Constructs the collection
     *
     * @param Array $init array of key, value pair to initialize our
     *                    collection with e.g. ['hello' => 'world']
     */
    function __construct($init = [])
    {
        $this->data = [];
        $this->setMultiple($init);
    }

    /**
     * Locks the collection against modifications
     *
     * @return nothing
     */
    public function lock()
    {
        $this->locked = true;
    }

    /**
     * Set Multiple items
     *
     * @param Array $data key, value pairs
     *
     * @return boolval true If successful in setting, false otherwise
     */
    public function setMultiple(Array $data)
    {
        if ($this->locked) return false;

        $result = true;
        
        foreach ($data as $k => $v) {
            $r = $this->set($k, $v);
            $result = $result && $r;
        }

        return $result;
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
    function set($id, $value)
    {
        if ($this->locked) return false;

        try {
            eval('$' . "this->data['" . str_replace(".", "']['", $id) . "']" . ' = $value;');
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
    function has($id)
    {
        $d = &$this->data;
        $k = explode('.', $id);

        foreach ($k as $kk) {            
            if (array_key_exists($kk, $d)) {
                $d = &$d[$kk];
            } else {
                return false;
            }
        }

        return true;    
    }

    /**
     * Finds an entry of the collection by its identifier and returns it.
     *
     * @param String $id      Identifier of the entry to look for.
     * @param Mixed  $default A default value.
     *
     * @return Mixed Entry.
     */
    function get($id, $default = null)
    {
        if (!$this->has($id)) return $default;

        eval('$value = $this->data' . "['" . str_replace('.', "']['", $id) . "'];");
        return $value;
    }

    /**
     * Removes the value from identified by an identifier from the colection
     *
     * @param String $id identifier
     *
     * @return boolval true if successful in removing, false otherwise
     */
    function remove($id)
    {
        if ($this->locked) return false;

        if ($this->has($id)) {
            eval('unset($this->data' . "['" . str_replace('.', "']['", $id) . "']);");
            return true;
        }

        return false;
    }

    /**
     * Returns all the items in the collection as raw array
     *
     * @return Array The collection's raw data
     */
    function toArray()
    {
        return $this->data;
    }

    /**
     * Get collection keys
     *
     * @return Array The collection's raw data keys
     */
    function keys()
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
    function offsetSet($id, $value)
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
    function offsetExists($id)
    {
        return $this->has($id);
    }

    /**
     * Get collection item for key
     *
     * @param String $id The data key
     *
     * @return Mixed The key's value, or null if does not exist
     */
    function offsetGet($id)
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
    function offsetUnset($id)
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
    function count()
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
    function getIterator()
    {
        return new ArrayIterator($this->data);
    }
}
