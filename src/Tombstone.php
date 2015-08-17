<?php
namespace Scheb\Tombstone;

class Tombstone
{
    /**
     * @var string
     */
    private $tombstoneDate;

    /**
     * @var string
     */
    private $author;

    /**
     * @var string
     */
    private $file;

    /**
     * @var int
     */
    private $line;

    /**
     * @var string
     */
    private $method;

    /**
     * @var Vampire[]
     */
    private $vampires = array();

    /**
     * @param string $tombstoneDate
     * @param string $author
     * @param string $file
     * @param int $line
     * @param string $method
     */
    public function __construct($tombstoneDate, $author, $file, $line, $method)
    {
        $this->tombstoneDate = $tombstoneDate;
        $this->author = $author;
        $this->file = $file;
        $this->line = $line;
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return string
     */
    public function getTombstoneDate()
    {
        return $this->tombstoneDate;
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->file.':'.$this->line;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param Vampire $vampire
     */
    public function addVampire(Vampire $vampire)
    {
        $this->vampires[] = $vampire;
    }

    /**
     * @return Vampire[]
     */
    public function getVampires()
    {
        return $this->vampires;
    }

    /**
     * @return bool
     */
    public function hasVampires()
    {
        return !$this->vampires;
    }
}