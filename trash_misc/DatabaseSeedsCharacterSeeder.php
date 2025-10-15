<?php
namespace Database\Seeds;

use Database\Seeder;
use Faker\Factory as Faker;
use Models\ORM\Character;

class CharacterSeeder implements Seeder{

    public function seed(): void
    {
        $rows = $this->createRowData();

        foreach ($rows as $data){
            Character::create($data);
        }
    }

    public function createRowData(): array
    {
        $faker = Faker::create();
        $rows = [];

        $classes = [
            'Warrior', 'Mage', 'Archer', 'Rogue',
            'Paladin', 'Cleric', 'Druid', 'Necromancer',
            'Bard', 'Monk', 'Ranger', 'Sorcerer',
            'Warlock', 'Alchemist', 'Assassin', 'Samurai',
            'Ninja', 'Summoner', 'Berserker', 'Knight'
        ];

        $races = [
            'Human', 'Elf', 'Dwarf', 'Orc', 'Halfling',
            'Gnome', 'Troll', 'Vampire', 'Werewolf',
            'Fairy', 'Centaur', 'Dragonkin'
        ];

        for ($i = 0; $i < 1000; $i++) {
            $rows[] = [
                'name'        => $faker->name,
                'class'       => $faker->randomElement($classes),
                'gender'      => $faker->numberBetween(0, 2), // Assuming 0: Not Specified, 1: Male, 2: Female
                'race'        => $faker->randomElement($races),
                'subclass'    => $faker->randomElement($classes),
                'description' => $faker->text,
                'body'        => $faker->numberBetween(1, 30)
            ];
        }

        return $rows;
    }
}
