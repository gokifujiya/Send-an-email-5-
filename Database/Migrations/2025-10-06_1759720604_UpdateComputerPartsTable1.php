<?php
namespace Database\Migrations;

use Database\SchemaMigration;

class UpdateComputerPartsTable1 implements SchemaMigration
{
    public function up(): array
    {
        return [
            "ALTER TABLE computer_parts ADD COLUMN submitted_by BIGINT",
            "ALTER TABLE computer_parts ADD CONSTRAINT fk_computer_parts_users FOREIGN KEY (submitted_by) REFERENCES users(id)"
        ];
    }

    public function down(): array
    {
        return [
            // 外部キー制約を削除します
            "ALTER TABLE computer_parts DROP FOREIGN KEY fk_computer_parts_users",
            // submitted_byカラムを削除します
            "ALTER TABLE computer_parts DROP COLUMN submitted_by"
        ];
    }
}
