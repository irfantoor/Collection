<?php
/**
 * IrfanTOOR\Collection\Adapter\AbstractAdapter
 * php version 7.3
 *
 * @package   IrfanTOOR\Collection
 * @author    Irfan TOOR <email@irfantoor.com>
 * @copyright 2020 Irfan TOOR
 */
namespace IrfanTOOR\Collection\Adapter;

/**
 * AbstractAdapter
 */
abstract class AbstractAdapter
{
    /**
     * Internal array to keep track of [$key => $value] pairs
     *
     * @var array
     */
    protected $data = [];

    /**
     * Semaphore to indicate that the collection is locked against modifications
     *
     * @var bool
     */
    protected $locked = false;

    /**
     * Collection constructor
     *
     * @param array $init array of key, value pair to initialize our
     *                    collection with e.g. ['hello' => 'world']
     */
    public function __construct(array $init = [])
    {
        $this->data = [];
        $this->setMultiple($init);
    }

    /**
     * @inheritdoc IrfanTOOR\Collection\Adapter\AdapterInterface::lock
     */
    public function lock()
    {
        $this->locked = true;
    }

    /**
     * @inheritdoc IrfanTOOR\Collection\Adapter\AdapterInterface::setMultiple
     */
    public function setMultiple(array $data): bool
    {
        if ($this->locked) { 
            return false;
        }

        $final_result = true;

        foreach ($data as $key => $value) {
            $result = $this->set($key, $value);
            $final_result = $final_result && $result;
        }

        return $final_result;
    }

    /**
     * @inheritdoc IrfanTOOR\Collection\Adapter\AdapterInterface::set
     */
    abstract public function set(string $key, $value): bool;

    /**
     * @inheritdoc IrfanTOOR\Collection\Adapter\AdapterInterface::has
     */
    abstract public function has(string $key): bool;

    /**
     * @inheritdoc IrfanTOOR\Collection\Adapter\AdapterInterface::get
     */
    abstract public function get(string $key, $default = null);

    /**
     * @inheritdoc IrfanTOOR\Collection\Adapter\AdapterInterface::remove
     */
    abstract public function remove(string $key): bool;

    /**
     * @inheritdoc IrfanTOOR\Collection\Adapter\AdapterInterface::toArray
     */
    function toArray(): array
    {
        return $this->data;
    }

    /**
     * @inheritdoc IrfanTOOR\Collection\Adapter\AdapterInterface::keys
     */
    function keys(): array
    {
        return array_keys($this->data);
    }

    /**
     * @inheritdoc IrfanTOOR\Collection\Adapter\AdapterInterface::count
     */
    public function count(): int
    {
        return count($this->data);
    }
}
