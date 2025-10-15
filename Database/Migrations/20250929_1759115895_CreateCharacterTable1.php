<?php
namespace Database\Migrations;

use Database\SchemaMigration;

class CreateCharacterTable1 implements SchemaMigration
{
    public function up(): array
    {
        return [
            "CREATE TABLE IF NOT EXISTS characters (
                id BIGINT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                class VARCHAR(255) NOT NULL,
                gender INT,
                race VARCHAR(255),
                subclass VARCHAR(255),
                description TEXT,
                body INT
            ) ENGINE=InnoDB;"
        ];
    }

    public function down(): array
    {
        return [
            "DROP TABLE IF EXISTS characters;"
        ];
    }
}

