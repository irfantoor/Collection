<?php
/**
 * ExtendedCollectionTest
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

class ExtendedCollectionTest extends Test
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

    function testInit()
    {
        $c = new Collection();
        $this->assertArray($c->toArray());
        $this->assertEquals([], $c->toArray());

        $c = $this->getCollection();
        $this->assertEquals('world!', $c->get('hello'));
        $this->assertEquals('My App', $c->get('app.name'));
        $this->assertEquals('1.1', $c->get('app.version'));

        $init = [
            'hello' => 'World!',
        ];

        $c = $this->getCollection($init);
        $this->assertEquals(1, $c->count());
        $this->assertEquals('World!', $c->get('hello'));
    }  

    function testVersion()
    {
        $c = $this->getCollection();
        $version = \IrfanTOOR\Collection::VERSION;
        $this->assertNotEmpty($c::VERSION);
        $this->assertString($c::VERSION);
        $this->assertEquals($version, $c::VERSION);
    }

    function testLocked()
    {
        $c = $this->getCollection();
        $c->lock();

        # set

        $result = $c->set('hello', 'someone');
        $this->assertFalse($result);
        $this->assertEquals('world!', $c->get('hello'));

        $result = $c->set('udefined', 'something');
        $this->assertFalse($result);
        $this->assertNull($c->get('undefiend'));

        $this->assertEquals(null, $c->get('something'));
        $this->assertEquals(null, $c['something']);
        $this->assertEquals('default', $c->get('something', 'default'));

        // set for the first time
        $c->set('something', 'defined');

        $this->assertNull($c->get('something'));
        $this->assertEquals('default', $c->get('something', 'default'));
        $this->assertEquals(null, $c['something']);

        // assign a new value
        $c->set('something', 'somethingelse');
        $this->assertEquals('default', $c->get('something', 'default'));

        // predfined
        $this->assertEquals('1.1', $c->get('app.version'));
        $c->set('app.version', '1.2');
        $this->assertEquals('1.1', $c->get('app.version'));
        $this->assertEquals('1.1', $c->get('app')['version']);

        // setting using array access

        // predefined
        $this->assertEquals('1.1', $c['app.version']);
        $c['app.version'] = '1.3';
        $this->assertEquals('1.1', $c['app.version']);
        $this->assertEquals('1.1', $c['app']['version']);

        // set for the first time
        $this->assertEquals(null, $c['certification']);
        $c['certification.authority'] = ['name' => 'CA', 'address' => 'somewhere' ];
        $this->assertNull($c['certification.authority.name']);
        $this->assertNull($c['certification.authority.address']);


        # remove

        $this->assertTrue($c->has('null'));
        $this->assertTrue($c->has('hello'));

        // remove an element using method 'remove'
        $c->remove('null');
        $c->remove('hello');
        $this->assertTrue($c->has('null'));
        $this->assertTrue($c->has('hello'));

        // remove another element using unset and array access
        unset($c['null']);
        unset($c['hello']);
        $this->assertTrue($c->has('null'));
        $this->assertTrue($c->has('hello'));

        // remove another element using unset and array access
        $this->assertTrue($c->has('app.version'));
        unset($c['app.version']);
        $this->assertTrue($c->has('app.name'));
        $this->assertTrue($c->has('app.version'));
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

        $c->setMultiple(
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

    function testFilter()
    {
        $c = $this->getCollection();
        $some_more = [
            1 => 1,
            2 => 'two',
            3 => 3,
        ];

        $c->setMultiple($some_more);

        $d = $c->filter(function ($value, $key) {
            return false;
        });

        $d = $c->filter(function () {
            return true;
        });

        $this->assertInstanceOf(Collection::class, $d);
        $this->assertEquals($c, $d);

        $d = $c->filter(function ($value, $key) {
            return is_int($key);
        });

        $this->assertEquals($some_more, $d->toArray());

        $d = $c->filter(function ($value, $key) {
            return is_array($value);
        });
       
        $this->assertEquals(1, $d->count());
        $this->assertFalse($d->has('null'));
        $this->assertFalse($d->has('hello'));
        $this->assertTrue($d->has('app'));
    }

    function testMap()
    {
        $c = $this->getCollection();
        $some_more = [
            1 => 1,
            2 => 'two',
            3 => 3,
        ];

        $c->setMultiple($some_more);

        $d = $c->map(function ($value) {
            return $value;
        });

        $this->assertInstanceOf(Collection::class, $d);
        $this->assertEquals($c, $d);

        $d = $c->filter(
            function ($value, $key) {
                return is_int($value);
            }
        )->map(
            function ($value) {
                return $value * $value;
            }
        );

        $this->assertEquals([1 => 1, 3 => 9], $d->toArray());
    }

    function testReduce()
    {
        $c = $this->getCollection();
        $some_more = [
            1 => 1,
            2 => 'two',
            3 => 3,
            4 => 16,
        ];

        $c->setMultiple($some_more);

        $d = $c->reduce(
            function ($carry, $value, $key) {
                return $carry;
            }
        );

        $this->assertNull($d);

        $c->setMultiple($some_more);

        $d = $c->reduce(
            function ($carry, $value, $key) {
                return 0;
            }
        );

        $this->assertZero($d);

        $c->setMultiple($some_more);

        $d = $c->reduce(
            function ($carry, $value, $key) {
                return is_int($value) ? $carry + (int) $value : $carry;
            }
        );

        $this->assertEquals(20, $d);

        $d = $c->reduce(function ($carry, $value, $key) {
            $total = 
                $carry +
                (is_int($value) ? $value : 0) -
                (is_int($key) ? $key : 0);

            return $total;
        });

        $this->assertEquals(10, $d);


        $d = $c->filter(
            function ($value, $key) {
                return is_int($value);
            }
        )->reduce(
            function ($carry, $value, $key) {
                return $carry + (int) $value - (int) $key;
            }
        );

        $this->assertInt($d);
        $this->assertEquals(12, $d);
    }
}
