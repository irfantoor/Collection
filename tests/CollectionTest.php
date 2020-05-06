<?php
/**
 * CollectionTest
 * php version 7.3
 *
 * @package   IrfanTOOR\Collection
 * @author    Irfan TOOR <email@irfantoor.com>
 * @copyright 2020 Irfan TOOR
 */

use IrfanTOOR\Collection;
use IrfanTOOR\Collection\Adapter\{
    AdapterInterface,
    ExtendedAdapter,
    SimpleAdapter,
};
use IrfanTOOR\Test;

class CollectionTest extends Test
{
    function getInit()
    {
        return [
            'null'  => null,
            'hello' => 'world!',
            'app'   => [
                'name'    => 'My App',
                'version' => '1.1',
            ]
        ];
    }

    function getCollection($init = null)
    {
        if (!$init) {
            $init = $this->getInit();
        }

        return new Collection($init);
    }

    function testCollectionInstance()
    {
        $c = $this->getCollection();

        $this->assertInstanceOf(Collection::class, $c);
    }

    function testAdapters()
    {
        $c = $this->getCollection();

        $this->assertInstanceOf(ExtendedAdapter::class, $c->getAdapter());

        $c->setAdapter(SimpleAdapter::class);
        $this->assertInstanceOf(SimpleAdapter::class, $c->getAdapter());

        # class doen not exists
        $this->assertException(
            function () use($c) {
                $c->setAdapter('BlaBlaClass');
            },
            Exception::class,
            "Class 'BlaBlaClass' not found"
        );
        
        # class not implementing AdapterInterface
        $this->assertException(
            function () use($c) {
                $c->setAdapter(StdClass::class);
            },
            Exception::class,
            "Adapter must implement the " . AdapterInterface::class
        );

        $s = ['hello' => 'world'];

        $this->assertException(
            function () use($c, $s) {
                $c->setAdapter($s);
            },
            Exception::class,
            "Adapter can either be classname or an object"
        );
    }
}
