<?php
/**
 * IrfanTOOR\Collection\Adapter\AdapterInterface
 * php version 7.3
 *
 * @package   IrfanTOOR\Collection
 * @author    Irfan TOOR <email@irfantoor.com>
 * @copyright 2020 Irfan TOOR
 */
namespace IrfanTOOR\Collection\Adapter;

/**
 * AdapterInterface
 */
interface AdapterInterface
{
    /**
     * Locks the collection against modifications
     *
     * @return void
     */
    public function lock();

    /**
     * Set multiple elements
     *
     * @param array $data Associative array of elements
     *
     * @return bool Result of the operation
     */
    public function setMultiple(array $data): bool;

    /**
     * Sets an element in the collection
     *
     * @param string $key   Key of the element
     * @param mixed  $value Value of element,
     *
     * @return bool Result of the operation, true if successful, false otherwise
     */
    public function set(string $key, $value): bool;

    /**
     * Verifies if the collection has an element
     *
     * @param string $key Key to look for
     *
     * @return bool Result of the operation, true if successful, false otherwise
     */
    public function has(string $key): bool;

    /**
     * Retrieves an element from the collection
     *
     * @param string $key     Key of the element to look for
     * @param mixed  $default Default to be returned, if element is not found
     *
     * @return null|mixed Null if not found or the element
     */
    public function get(string $key, $default = null);

    /**
     * Removes an element from the collection
     *
     * @param string $key Key of the element to look for
     *
     * @return bool Result of the operation, true if successful, false otherwise
     */
    public function remove(string $key): bool;

    /**
     * Returns all the items in the collection as associative array
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * Retrieves the keys of all the elements in the collection
     *
     * @return array
     */
    public function keys(): array;

    /**
     * Retrieves the count of elements in the collection
     *
     * @return int
     */
    public function count(): int;
}
