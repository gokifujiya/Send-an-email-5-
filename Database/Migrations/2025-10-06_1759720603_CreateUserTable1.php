<?php
namespace Database\Migrations;

use Database\SchemaMigration;

class CreateUserTable1 implements SchemaMigration
{
    public function up(): array
    {
        return [
            // Ensure InnoDB so FK works later
            "CREATE TABLE IF NOT EXISTS users (
                id BIGINT PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                email_confirmed_at VARCHAR(255),
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB"
        ];
    }

    public function down(): array
    {
        return [
            "DROP TABLE IF EXISTS users"
        ];
    }
}

