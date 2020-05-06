<?php
/**
 * IrfanTOOR\Collection\Adapter\SimpleAdapter
 * php version 7.3
 *
 * @package   IrfanTOOR\Collection
 * @author    Irfan TOOR <email@irfantoor.com>
 * @copyright 2020 Irfan TOOR
 */
namespace IrfanTOOR\Collection\Adapter;

use IrfanTOOR\Collection\Adapter\{
    AbstractAdapter,
    AdapterInterface,
};

/**
 * SimpleAdapter helps constructing the collection, which can include dot '.' in the
 * keys, hence can not be accessed using dotted notation. Therefore, it is optimized
 * to be single level deep. Accessing the elements multiple level deep is a little
 * tricky, as shown in the example.
 *
 * Example:
 *      $c = new Collection([
 *          'debug' => [
 *              'level' => 2
 *          ]
 *      ], new SimpleAdapter());
 *
 *      $level = $c->get('debug')['level']; # will work, though its complicated
 *      $level = $c['debug.level'];         # will not work it assumes 'debug.level'
 *                                            is one key
 *      $level = $c['debug']['level'];      # will work and its simple!
 */
class SimpleAdapter extends AbstractAdapter implements AdapterInterface
{
    /**
     * @inheritdoc IrfanTOOR\Collection\Adapter\AdapterInterface::set
     */
    public function set(string $key, $value): bool
    {
        if ($this->locked) {
            return false;
        }

        try {
            $this->data[$key] = $value;
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * @inheritdoc IrfanTOOR\Collection\Adapter\AdapterInterface::has
     */
    public function has(string $key): bool
    {
        return (is_string($key)) ? array_key_exists($key, $this->data) : false;
    }

    /**
     * @inheritdoc IrfanTOOR\Collection\Adapter\AdapterInterface::get
     */
    public function get(string $key, $default = null)
    {
        return $this->has($key) ? $this->data[$key] : $default;
    }

    /**
     * @inheritdoc IrfanTOOR\Collection\Adapter\AdapterInterface::remove
     */
    public function remove(string $key): bool
    {
        if ($this->locked) {
            return false;
        }

        if ($this->has($key)) {
            unset($this->data[$key]);
            return true;
        }

        return false;
    }
}
