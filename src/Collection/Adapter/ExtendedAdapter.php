<?php
/**
 * IrfanTOOR\Collection\Adapter\ExtendedAdapter
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
 * ExtendedAdapter helps constructing a collection in which we can acccess the
 * values multiple level deep by using dotted notation
 *
 * Example:
 *      $c = new Collection([
 *          'debug' => [
 *              'level' => 2
 *          ]
 *      ]);
 *
 *      $level = $c->get('debug.level');  # will work
 *      $level = $c['debug.level'];       # will work
 *      $level = $c['debug']['level']     # will also work, ( and it is 
 *                                          consistant with SimpleAdapter)
 */
class ExtendedAdapter extends AbstractAdapter implements AdapterInterface
{
    /**
     * @doc IrfanTOOR\Collection\Adapter\AbstractAdapter::set
     */
    public function set(string $key, $value): bool
    {
        if ($this->locked) {
            return false;
        }

        try {
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
     * @doc IrfanTOOR\Collection\Adapter\AbstractAdapter::has
     */
    public function has(string $key): bool
    {
        $d = &$this->data;
        $k = explode('.', $key);

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
     * @doc IrfanTOOR\Collection\Adapter\AbstractAdapter::get
     */
    public function get(string $key, $default = null)
    {
        if (!$this->has($key)) {
            return $default;
        }

        eval(
            '$value' .
            ' = ' . 
            '$this->data' . "['" . str_replace('.', "']['", $key) . "'];"
        );

        return $value;
    }

    /**
     * @doc IrfanTOOR\Collection\Adapter\AbstractAdapter::remove
     */
    public function remove(string $key): bool
    {
        if ($this->locked) {
            return false;
        }

        if ($this->has($key)) {
            eval(
                'unset($this->data' . 
                "['" . str_replace('.', "']['", $key) . 
                "']);"
            );
            return true;
        }

        return false;
    }
}
