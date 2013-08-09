<?php

namespace Lyrixx\Ratp;

class Stop
{
    private $line;
    private $name;
    private $type;
    private $directions;

    public function __construct($line, $name, $type)
    {
        $this->line = $line;
        $this->name = $name;
        $this->type = $type;
        $this->directions = array();
    }

    public function getLine()
    {
        return $this->line;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function hasDirections()
    {
        return 0 < count($this->directions);
    }

    public function getDirections()
    {
        return $this->directions;
    }

    public function hasDirection($directionName)
    {
        return array_key_exists($directionName, $this->directions);
    }

    public function addDirection($directionName)
    {
        $this->directions[$directionName] = new Direction($directionName);

        return $this;
    }

    public function getDirection($directionName)
    {
        if (!array_key_exists($directionName, $this->directions)) {
            throw new \InvalidArgumentException(sprintf('The direction "%s" does not exist', $directionName));
        }

        // var_dump($this->directions);
        return $this->directions[$directionName];
    }
}
