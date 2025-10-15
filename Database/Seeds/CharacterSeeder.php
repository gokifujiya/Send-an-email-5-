<?php
namespace Database\Seeds;

use Database\Seeder;
use Faker\Factory as Faker;
use Models\ORM\Character;

class CharacterSeeder implements Seeder
{
    public function seed(): void
    {
        $faker = Faker::create();

        $classes = ['Warrior','Mage','Archer','Rogue','Paladin','Cleric','Druid','Necromancer',
                    'Bard','Monk','Ranger','Sorcerer','Warlock','Alchemist','Assassin','Samurai',
                    'Ninja','Summoner','Berserker','Knight'];
        $races = ['Human','Elf','Dwarf','Orc','Halfling','Gnome','Troll','Vampire','Werewolf',
                  'Fairy','Centaur','Dragonkin'];

        // default 50, override with env var
        $count = (int)(getenv('SEED_COUNT') ?: 50);

        for ($i = 0; $i < $count; $i++) {
            Character::create([
                'name'        => $faker->name(),
                'class'       => $faker->randomElement($classes),
                'gender'      => $faker->numberBetween(0, 2),
                'race'        => $faker->randomElement($races),
                'subclass'    => $faker->randomElement($classes),
                'description' => $faker->text(),
                'body'        => $faker->numberBetween(1, 30),
            ]);
        }
    }

    // still required by Seeder interface
    public function createRowData(): array
    {
        return [];
    }
}

