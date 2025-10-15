<?php
namespace Models\ORM;

use Database\DataAccess\ORM;

class Head extends ORM
{
    // Optional: convenience accessor to parent
    public function character(): ?Character
    {
        return $this->belongsTo(Character::class, foreignKey: 'character_id');
    }
}
