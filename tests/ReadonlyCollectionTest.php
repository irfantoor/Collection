<?php
/**
 * IrfanTOOR\Collection
 *
 * @author    Irfan TOOR <email@irfantoor.com>
 * @copyright 2017 Irfan TOOR
 * @license   https://github.com/irfantoor/collection/blob/master/LICENSE (MIT License)
 * @link      https://github.com/irfantoor/collection/tests/ReadonlyCollectionTest.php
 */

use IrfanTOOR\Collection\ReadonlyCollection;
use IrfanTOOR\Test;

class ReadonlyCollectionTest extends Test
{
    function getReadonlyCollection($init = null)
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

        return new ReadonlyCollection($init);
    }

    function testCollectionInstance()
    {
        $c = $this->getReadonlyCollection();
        $this->assertInstanceOf(IrfanTOOR\Collection\ReadonlyCollection::class, $c);
        $this->assertInstanceOf(IrfanTOOR\Collection::class, $c);
    }


    function testInit()
    {
        $c = new ReadonlyCollection();
        $this->assertArray($c->toArray());
        $this->assertEquals([], $c->toArray());

        $c = $this->getReadonlyCollection($init);
        $this->assertEquals('world!', $c->get('hello'));
        $this->assertEquals('My App', $c->get('app.name'));
        $this->assertEquals('1.1', $c->get('app.version'));

        $init = [
            'hello' => 'World!',
        ];

        $c = $this->getReadonlyCollection($init);
        $this->assertEquals(1, $c->count());
        $this->assertEquals('World!', $c->get('hello'));
    }

    function testSet()
    {
        $c = $this->getReadonlyCollection();

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
    }

    function testRemove()
    {
        $c = $this->getReadonlyCollection();

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
}
