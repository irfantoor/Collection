<?php
/**
 * IrfanTOOR\Collection\Adapter\PersistantAdapter
 * php version 7.3
 *
 * @package   IrfanTOOR\Collection
 * @author    Irfan TOOR <email@irfantoor.com>
 * @copyright 2020 Irfan TOOR
 */
namespace IrfanTOOR\Collection\Adapter;

use Exception;

use IrfanTOOR\Collection\Adapter\{
    SimpleAdapter,
    AdapterInterface,
};
use IrfanTOOR\Collection\Storage;

/**
 * PersistantAdapter helps building a SimpleCollection which can be persisted
 * to a file or to storage system using favourite filesystem
 */
class PersistantAdapter extends SimpleAdapter implements AdapterInterface
{
    /**
     * Filesystem
     * 
     * @var Filesystem
     */
    protected $file_system = null;

    /**
     * Filename
     *
     * @var string
     */
    protected $file = null;

    /**
     * Sets the filesystem to manage the backend storage file
     *
     * NOTE: It gives the benefit of using local, remote or cloud storage
     *
     * @param object $file_system FileSystem object which must have the
     *                            methods: have, read and write
     *
     * @return void
     */
    public function setFileSystem($file_system)
    {
        $this->file_system = $file_system;
    }

    /**
     * Sets the file to be used as persistant storage
     *
     * NOTE: In case a FileSystem object is used to manage the backend storage,
     *       it must be defined before defining the file
     *
     * @param string $file  Basename or filename including the path
     * @param bool   $reset If true reset the storage with the current data, 
     *                      and even create the file if does not exist
     *                      merge the data othewise
     *
     * @throws Exception If the file is not present
     *
     * @return void
     */
    public function setFile(string $file, bool $reset = false)
    {
        $file_absent = false;

        if ($this->file_system) {
            if (!$this->file_system->has($file)) {
                if ($reset) {
                    $this->file_system->write($file, '[]');
                } else {
                    $file_absent = true;
                }
            }
        } else {
            if (!file_exists($file)) {
                if ($reset) {
                    file_put_contents($file, '[]');
                } else {
                    $file_absent = true;
                }
            }
        }

        if ($file_absent) {
            throw new Exception(
                'File: ' . $file . 
                ', does not exist, use the parameter $reset = true to create'
            );
        }

        $this->file = $file;
        $this->sync($reset);
    }

    /**
     * Syncs the data with the storage
     *
     * @param bool $reset If true reset the storage with the current data,
     *                    it will even create the file if its not present,
     *                    merge the data to existing data othewise
     *
     * @throws Exception If the file has not be defined yet
     *
     * @return void
     */
    public function sync(bool $reset = false)
    {
        if (!$this->file) {
            throw new Exception(
                "File for persistant storage not defined, use setFile($file)"
            );
        }

        if (!$reset) {
            $contents = $this->file_system 
            ? $this->file_system->read($this->file)
            : file_get_contents($this->file);
        
            $file_data = json_decode($contents, 1);

            if (!is_array($file_data)) {
                $file_data = [];
            }            

            foreach ($this->data as $k => $v) {
                $file_data[$k] = $v;
            }

            $data = $file_data;
        } else {
            $data = $this->data;
        }

        $json = json_encode($data);

        if ($this->file_system) {
            $this->file_system->update($this->file, $json);
        } else {
            file_put_contents($this->file, $json);
        }

        // to make sure we return whats written to the file
        $this->data = json_decode($json, 1);
    }
}
