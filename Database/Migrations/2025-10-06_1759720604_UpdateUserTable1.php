<?php
namespace Database\Migrations;

use Database\SchemaMigration;

class UpdateUserTable1 implements SchemaMigration
{
    public function up(): array
    {
        return [
            "ALTER TABLE users ADD COLUMN company VARCHAR(255)"
        ];
    }

    public function down(): array
    {
        return [
            "ALTER TABLE users DROP COLUMN company"
        ];
    }
}
