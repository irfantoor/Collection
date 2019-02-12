<?php
/**
 * ReadOnlyCollection
 * php version 7.0
 *
 * @category  Collection
 * @package   IrfanTOOR_Collection
 * @author    Irfan TOOR <email@irfantoor.com>
 * @copyright 2017 Irfan TOOR
 * @license   https://github.com/irfantoor/collection/blob/master/LICENSE (MIT)
 * @link      https://github.com/irfantoor/collection/blob/master/src/Collection/ReadonlyCollection.php
 */

namespace IrfanTOOR\Collection;

use IrfanTOOR\Collection;

/**
 * ReadonlyCollection implements a collection, which is locked after initialisation
 *
 * @category  Collection
 * @package   IrfanTOOR_Collection
 * @author    Irfan TOOR <email@irfantoor.com>
 * @copyright 2017 Irfan TOOR
 * @license   https://github.com/irfantoor/collection/blob/master/LICENSE (MIT)
 * @link      https://github.com/irfantoor/collection/blob/master/src/Collection/ReadonlyCollection.php
 */
class ReadonlyCollection extends Collection
{
    /**
     * Semaphore to indicate that the data is locked
     *
     * @var bool
     */
    protected $locked = false;

    // =========================================================================
    // ReadonlyCollection
    // =========================================================================

    /**
     * Constructs the readonly collection
     *
     * @param Array $init array of key, value pair to initialize our
     *                    collection with e.g. ['hello' => 'world']
     */
    public function __construct($init = [])
    {
        parent::__construct($init);
        $this->locked = true;
    }

    /**
     * Sets an $identifier and its Value pair, during init only
     *
     * @param String $id    identifier
     * @param Mixed  $value value of identifier
     *
     * @return boolval true If successful in setting, false otherwise
     */
    public function setItem($id, $value)
    {
        if ($this->locked) {
            return false;
        }

        return parent::setItem($id, $value);
    }


    /**
     * Overrides remove function of collection to avoid removal
     *
     * @param String $id identifier
     *
     * @return boolval always false to indicate id was not removed
     */
    public function remove($id)
    {
        return false;
    }
}
