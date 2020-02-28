<?php

use IrfanTOOR\Collection;
use IrfanTOOR\SingleLevelCollection;
use IrfanTOOR\Test;

class SingleLevelCollectionTest extends Test
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

        return new SingleLevelCollection($init);
    }

    function testCollectionInstance()
    {
        $c = $this->getCollection();
        $this->assertInstanceOf(SingleLevelCollection::class, $c);
        $this->assertInstanceOf(Collection::class, $c);
    }

    function testNoDotNotation()
    {
        $c = new SingleLevelCollection();
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
