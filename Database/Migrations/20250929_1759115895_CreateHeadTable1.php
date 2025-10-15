<?php
namespace Database\Migrations;

use Database\SchemaMigration;

class CreateHeadTable1 implements SchemaMigration
{
    public function up(): array
    {
        return [
            "CREATE TABLE IF NOT EXISTS heads (
                id BIGINT PRIMARY KEY AUTO_INCREMENT,
                character_id BIGINT UNIQUE,
                eye INT,
                nose INT,
                chin INT,
                hair INT,
                eyebrows INT,
                hair_color INT,
                FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE
            ) ENGINE=InnoDB;"
        ];
    }

    public function down(): array
    {
        return [
            "DROP TABLE IF EXISTS heads;"
        ];
    }
}

