<?php
namespace Database\DataAccess\Interfaces;

use Models\ComputerPart;

interface ComputerPartDAO
{
    public function getById(int $id): ?ComputerPart;

    // whichever your codebase uses—support both in the impl
    public function getRandomPart(): ?ComputerPart; // or getRandom()

    // needed for the POST update route
    public function update(ComputerPart $part): bool;
}

