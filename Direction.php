<?php

namespace Lyrixx\Ratp;

class Direction
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
        $this->schedule = new Schedule();
    }

    public function getName()
    {
        return $this->name;
    }

    public function getSchedule()
    {
        return $this->schedule;
    }

    public function addWaitingTime($waitingTime)
    {
        $this->schedule->push($waitingTime);

        return $this;
    }
}
