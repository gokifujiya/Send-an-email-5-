<?php
namespace Models\ORM;

use Database\DataAccess\ORM;

class Character extends ORM
{
    // defaults are fine (table name: characters, pk: id)
    // override static $primaryKey or getTableName() if you need different names
}
