<?php
namespace Models\ORM;

use Database\DataAccess\ORM;

class Character extends ORM
{
    public function profile(): string
    {
        return sprintf(
            "Name: %s\nDescription: %s\nGender: %s\nSubclass: %s\nRace: %s",
            $this->name,
            $this->description,
            $this->gender,
            $this->subclass,
            $this->race
        );
    }
}
