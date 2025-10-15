<?php
namespace Database\Seeds;

use Database\Seeder;
use Faker\Factory as Faker;
use Models\ORM\Character;
use Models\ORM\Head;

class HeadSeeder implements Seeder
{
    public function seed(): void
    {
        $faker = Faker::create();

        $chunk = 200;   // tune as needed
        $offset = 0;

        while (true) {
            $batch = Character::getAll($chunk, $offset);
            if (empty($batch)) break;

            foreach ($batch as $char) {
                $existing = $char->hasOne(Head::class, foreignKey: 'character_id');
                if ($existing) continue;

                Head::create([
                    'character_id' => $char->id,
                    'eye'          => $faker->numberBetween(0, 10),
                    'nose'         => $faker->numberBetween(0, 10),
                    'chin'         => $faker->numberBetween(0, 10),
                    'hair'         => $faker->numberBetween(0, 10),
                    'eyebrows'     => $faker->numberBetween(0, 10),
                    'hair_color'   => $faker->numberBetween(0, 10),
                ]);
            }

            $offset += $chunk;
        }
    }

    public function createRowData(): array { return []; }
}
