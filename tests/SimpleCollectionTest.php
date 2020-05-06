<?php
/**
 * SimpleCollectionTest
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

class SimpleCollectionTest extends Test
{
    function getCollection($init = null)
    {
        if ($init === null) {
            $init = [
                'null'  => null,
                'hello' => 'world!',
                'app'   => [
                    'name'    => 'My App',
                    'version' => '1.1',
                ]
            ];
        }

        return new Collection($init, new SimpleAdapter());
    }

    function testCollectionInstance()
    {
        $c = $this->getCollection();
        $this->assertInstanceOf(Collection::class, $c);
        $this->assertInstanceOf(SimpleAdapter::class, $c->getAdapter());
    }

    function testNoDotNotation()
    {
        $c = new Collection([], new SimpleAdapter());

        $c->set('hello', ['world' => 'something']);
        $this->assertArray($c['hello']);

        $this->assertNull($c['hello.world']);
        $this->assertFalse(array_key_exists('hello.world', $c->toArray()));

        $c->set('hello.world', 'hello world!');
        $this->assertTrue(array_key_exists('hello.world', $c->toArray()));
        $this->assertEquals('hello world!', $c['hello.world']);
        $this->assertEquals(2, $c->count());

        $c->remove('hello');
        $this->assertTrue(array_key_exists('hello.world', $c->toArray()));
        $this->assertEquals('hello world!', $c['hello.world']);
        $this->assertEquals(1, $c->count());
    }  
}
