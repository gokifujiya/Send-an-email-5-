<?php
namespace Commands\Programs;

use Exception;

class SeedDao
{
    public static function getAlias(): string {
        return 'seed-dao';
    }

    public static function getHelp(): string {
        return "Usage: php console seed-dao\n";
    }

    public function execute(array $args = []): int
    {
        echo "Starting DAO seeding...\n";
        $this->runAllSeeds();
        echo "Seeding completed!\n";
        return 0;
    }

    private function runAllSeeds(): void
    {
        $seedPath = __DIR__ . '/../../Database/SeedsDao';
        $files = glob($seedPath . '/*.php');

        foreach ($files as $file) {
            require_once $file;
            $className = 'Database\\SeedsDao\\' . basename($file, '.php');

            if (class_exists($className)) {
                $seeder = new $className();
                if (method_exists($seeder, 'seed')) {
                    echo "Running seeder: $className\n";
                    $seeder->seed();
                } else {
                    throw new Exception("Seeder $className missing seed() method");
                }
            }
        }
    }
}

