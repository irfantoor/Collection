<?php
/**
 * IrfanTOOR\Collection
 *
 * @author    Irfan TOOR <email@irfantoor.com>
 * @copyright 2017 Irfan TOOR
 * @license   https://github.com/irfantoor/collection/blob/master/LICENSE (MIT License)
 * @link      https://github.com/irfantoor/collection/tests/CollectionTest.php
 */

use IrfanTOOR\Collection;
use PHPUnit\Framework\TestCase;

class CllectionTest extends TestCase
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

        return new Collection($init);
    }

    function testCollectionInstance()
    {
        $c = $this->getCollection();
        $this->assertInstanceOf('IrfanTOOR\Collection', $c);
    }

    function testHas()
    {
        $c = $this->getCollection();

        // defined elements
            // using method
            $this->assertTrue($c->has('null'));
            $this->assertTrue($c->has('hello'));
            $this->assertTrue($c->has('app.name'));
            $this->assertTrue($c->has('app.version'));

            // using array access
            $this->assertTrue(isset($c['null']));
            $this->assertTrue(isset($c['hello']));
            $this->assertTrue(isset($c['app.name']));
            $this->assertTrue(isset($c['app.version']));

        // undefined elements

            // using method
            $this->assertFalse($c->has('nothing'));
            $this->assertFalse($c->has('app.author'));

            // using array access
            $this->assertFalse(isset($c['nothing']));
            $this->assertFalse(isset($c['app.author']));
    }

    function testGet()
    {
        $c = $this->getCollection();

        // defined elements
        $this->assertEquals(null,     $c->get('null'));
        $this->assertEquals('world!', $c->get('hello'));
        $this->assertEquals('My App', $c->get('app.name'));
        $this->assertEquals('1.1',    $c->get('app.version'));

        $this->assertEquals(null,     $c['null']);
        $this->assertEquals('world!', $c['hello']);
        $this->assertEquals('My App', $c['app.name']);
        $this->assertEquals('1.1',    $c['app.version']);

        // undefined elements
        $this->assertEquals(null, $c->get('something'));
        $this->assertEquals(null, $c->get('undefined'));
        $this->assertEquals(null, $c->get('app.author'));

        $this->assertEquals(null, $c['something']);
        $this->assertEquals(null, $c['undefined']);
        $this->assertEquals(null, $c['app.author']);

        // default behaviour
        $this->assertEquals(null,          $c->get('null',      'default'));
        $this->assertEquals('world!',      $c->get('hello',     'now-default'));
        $this->assertEquals('default',     $c->get('something', 'default'));
        $this->assertEquals('now-default', $c->get('undefined', 'now-default'));

        // attention when the value stored is *null* use isset instead of relying
        // on the return value, which is null in case of id 'null'.
        $this->assertEquals(null, isset($c['null']) ? $c['null']: 'default');
        $this->assertEquals('it', $c['app.author'] ?:  'it');
    }

    function testSet()
    {
        $c = $this->getCollection();

        $this->assertEquals(null, $c->get('something'));
        $this->assertEquals(null, $c['something']);
        $this->assertEquals('default', $c->get('something', 'default'));

        // set for the first time
        $c->set('something', 'defined');

        $this->assertEquals('defined', $c->get('something'));
        $this->assertEquals('defined', $c->get('something', 'default'));
        $this->assertEquals('defined', $c['something']);

        // assign a new value
        $c->set('something', 'somethingelse');
        $this->assertEquals('somethingelse', $c->get('something', 'default'));

        // predfined
        $this->assertEquals('1.1', $c->get('app.version'));
        $c->set('app.version', '1.2');
        $this->assertEquals('1.2', $c->get('app.version'));
        $this->assertEquals('1.2', $c->get('app')['version']);

        // setting using array access

        // predefined
        $this->assertEquals('1.2', $c['app.version']);
        $c['app.version'] = '1.3';
        $this->assertEquals('1.3', $c['app.version']);
        $this->assertEquals('1.3', $c['app']['version']);

        // set for the first time
        $this->assertEquals(null, $c['certification']);
        $c['certification.authority'] = ['name' => 'CA', 'address' => 'somewhere' ];
        $this->assertEquals('CA', $c['certification.authority.name']);
        $this->assertEquals('somewhere', $c['certification.authority.address']);
    }

    function testSetMultipleUsingAnArray()
    {
        $c = $this->getCollection();

        $this->assertEquals(null, $c['something']);
        $this->assertEquals(null, $c['undefined']);

        $c->set(
            [
                'something' => 'defined',
                'undefined' => 'now-defined'
            ]
        );

        $this->assertEquals('defined', $c['something']);
        $this->assertEquals('now-defined', $c->get('undefined', 'default'));
    }

    function testRemove()
    {
        $c = $this->getCollection();

        $this->assertTrue($c->has('null'));
        $this->assertTrue($c->has('hello'));

        // remove an element using method 'remove'
        $c->remove('null');
        $this->assertFalse($c->has('null'));
        $this->assertTrue($c->has('hello'));

        // remove another element using unset and array access
        unset($c['hello']);
        $this->assertFalse($c->has('null'));
        $this->assertFalse($c->has('hello'));

        // remove another element using unset and array access
        $this->assertTrue($c->has('app.version'));
        unset($c['app.version']);
        $this->assertTrue($c->has('app.name'));
        $this->assertFalse($c->has('app.version'));

    }

    function testToArray()
    {
        $init = [
            'null'  => null,
            'hello' => 'world!',
            'array' => [
                'a' => 'A',
                'b' => 'B'
            ]
        ];

        $c = $this->getCollection($init);
        $a = $c->toArray();

        $this->assertEquals($init, $a);
    }

    function testKeys()
    {
        $init = [
            'null'  => null,
            'hello' => 'world!',
            'array' => [
                'a' => 'A',
                'b' => 'B'
            ]
        ];

        $c = $this->getCollection($init);
        $keys = $c->keys();

        $this->assertEquals(array_keys($init), $keys);
    }

    function testCount()
    {
        $init = [
            'null'  => null,
            'hello' => 'world!',
            'array' => [
                'a' => 'A',
                'b' => 'B'
            ]
        ];

        $c = $this->getCollection($init);

        $this->assertEquals(3, $c->count());

        unset($c['array.a']);
        $this->assertEquals(3, $c->count());

        unset($c['array']);
        $this->assertEquals(2, $c->count());

        $c->remove('null');
        $this->assertEquals(1, $c->count());
    }
}
