<?php
/**
 * PersistantCollectionTest
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
    PersistantAdapter,
};
use IrfanTOOR\Test;

use League\Flysystem\FileSystem;
use League\Flysystem\Adapter\Local;

class PersistantCollectionTest extends Test
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

    function getFile()
    {
        $file = __DIR__ . '/storage/persistant.json';
        
        if (file_exists($file)) {
            unlink($file);
        }

        return $file;
    }

    function getCollection($init = null)
    {
        if ($init === null) {
            $init = $this->getInit();
        }

        return new Collection($init, new PersistantAdapter());
    }

    function testCollectionInstance()
    {
        $c = $this->getCollection();
        $this->assertInstanceOf(Collection::class, $c);
        $adapter = $c->getAdapter();
        $this->assertInstanceOf(PersistantAdapter::class, $adapter);
        $this->assertInstanceOf(SimpleAdapter::class, $adapter);
    }

    function testFileStorage()
    {
        $file = $this->getFile();
        $path = dirname($file) . '/';
        $basename = basename($file);
        $c = $this->getCollection();
        
        # if file is not present
        $this->assertException(
            function () use ($c, $file) {
                $c->setFile($file);
            },
            Exception::class,
            'File: ' . $file . ', does not exist, use the parameter $reset = true to create'
        );

        $c->setFile($file, true); # auto-syncs

        $expected = $this->getInit();
        $this->assertEquals($expected, $c->toArray());

        # check if it persists
        $c = new Collection([], new PersistantAdapter());
        $c->setFile($file);
        $this->assertEquals($expected, $c->toArray());

        # modifications are not auto-synced
        $c[1] = 1;
        $c[2] = 2;
        $c[0] = 0;
        $expected = $c->toArray();
        $this->assertNotEquals($expected, json_decode(file_get_contents($file), 1));

        # the data is same after syncStorage is called
        $c->sync();
        $this->assertEquals($expected, json_decode(file_get_contents($file), 1));
        
        $updated = $expected;
        $updated['hello'] = 'new world';

        file_put_contents($file, json_encode($updated));
        $expected = $c->toArray();
        $this->assertNotEquals($expected, json_decode(file_get_contents($file), 1));

        $c->sync();

        $expected = $c->toArray();
        $this->assertEquals($expected, json_decode(file_get_contents($file), 1));
    }

    function testFileSystemStorage()
    {
        $file = $this->getFile();
        $path = dirname($file) . '/';
        $basename = basename($file);

        $c = $this->getCollection();
        
        $this->assertException(
            function () use ($c, $basename) {
                $c->setFile($basename);
            },
            Exception::class,
            'File: ' . $basename . ', does not exist, use the parameter $reset = true to create'
        );

        $file_system = new FileSystem(new Local($path));
        $c->setFileSystem($file_system);
        $c->setFile($basename, true);

        $expected = $this->getInit();
        $this->assertEquals($expected, $c->toArray());

        # check if it persists
        $c = new Collection([], new PersistantAdapter());
        $file_system = new FileSystem(new Local($path));
        $c->setFileSystem($file_system);
        $c->setFile($basename);

        # modifications are not auto-synced
        $c[1] = 1;
        $c[2] = 2;
        $c[0] = 0;
        $expected = $c->toArray();
        $this->assertNotEquals($expected, json_decode(file_get_contents($file), 1));

        # the data is same after syncStorage is called
        $c->sync();
        $this->assertEquals($expected, json_decode(file_get_contents($file), 1));
        
        $updated = $expected;
        $updated['hello'] = 'new world';

        file_put_contents($file, json_encode($updated));
        $expected = $c->toArray();
        $this->assertNotEquals($expected, json_decode(file_get_contents($file), 1));

        $c->sync();

        $expected = $c->toArray();
        $this->assertEquals($expected, json_decode(file_get_contents($file), 1));
    }    
}
