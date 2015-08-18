<?php
namespace Scheb\Tombstone\Analyzer;

use Scheb\Tombstone\Tombstone;

class TombstoneList implements \Countable, \Iterator
{

    /**
     * @var Tombstone[]
     */
    private $tombstones = array();

    /**
     * @var Tombstone[]
     */
    private $fileLineIndex = array();

    /**
     * @var Tombstone[][]
     */
    private $methodIndex = array();

    /**
     * @param Tombstone $tombstone
     */
    public function addTombstone(Tombstone $tombstone)
    {
        $this->tombstones[] = $tombstone;
        $this->fileLineIndex[$tombstone->getPosition()] = $tombstone;
        $methodName = $tombstone->getMethod();
        if (!isset($this->methodIndex[$methodName])) {
            $this->methodIndex[$methodName] = array();
        }
        $this->methodIndex[$methodName][] = $tombstone;
    }

    /**
     * @param string $method
     * @return Tombstone[]
     */
    public function getInMethod($method)
    {
        if (isset($this->methodIndex[$method])) {
            return $this->methodIndex[$method];
        }

        return null;
    }

    /**
     * @param string $file
     * @param int $line
     * @return Tombstone
     */
    public function getInFileAndLine($file, $line)
    {
        $pos = Tombstone::createPosition($file, $line);
        if (isset($this->fileLineIndex[$pos])) {
            return $this->fileLineIndex[$pos];
        }

        return null;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->fileLineIndex);
    }

    /**
     * @return Tombstone
     */
    public function current()
    {
        return current($this->tombstones);
    }

    public function next()
    {
        next($this->tombstones);
    }

    /**
     * @return int
     */
    public function key()
    {
        return key($this->tombstones);
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return isset($this->tombstones[$this->key()]);
    }

    public function rewind()
    {
        reset($this->tombstones);
    }
}
