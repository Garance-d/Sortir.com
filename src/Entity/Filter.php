<?php

namespace App\Entity;

class Filter
{
    private $campus;
    private $event;
    private $date;
    private $eventCheckb;

    public function getCampus()
    {
        return $this->campus;
    }

    public function setCampus($campus): self
    {
        $this->campus = $campus;
        return $this;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function setEvent($event): self
    {
        $this->event = $event;
        return $this;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($date): self
    {
        $this->date = $date;
        return $this;
    }

    public function isEventCheckb()
    {
        return $this->eventCheckb;
    }

    public function setEventCheckb($eventCheckb): self
    {
        $this->eventCheckb = $eventCheckb;
        return $this;
    }
}