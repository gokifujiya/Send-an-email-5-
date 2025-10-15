<?php
namespace Database\DataAccess\Implementations;

use Database\DataAccess\Interfaces\ComputerPartDAO;
use Database\DatabaseManager;
use Models\ComputerPart;
use Models\DataTimeStamp;

class ComputerPartDAOImpl implements ComputerPartDAO
{
    public function create(ComputerPart $partData): bool
    {
        if ($partData->getId() !== null) {
            throw new \Exception('Cannot create a computer part with an existing ID. id: ' . $partData->getId());
        }
        return $this->createOrUpdate($partData);
    }

    public function getById(int $id): ?ComputerPart
    {
        $mysqli = DatabaseManager::getMysqliConnection();
        $row = $mysqli->prepareAndFetchAll(
            "SELECT * FROM computer_parts WHERE id = ?",
            'i',
            [$id]
        )[0] ?? null;

        return $row ? $this->resultToComputerPart($row) : null;
    }

    public function update(ComputerPart $part): bool
    {
        // Update the extended schema (no legacy price/length/width/height fields)
        $mysqli = DatabaseManager::getMysqliConnection();
        return $mysqli->prepareAndExecute(
            "UPDATE computer_parts
               SET name = ?, type = ?, brand = ?, model_number = ?, release_date = ?,
                   description = ?, performance_score = ?, market_price = ?, rsm = ?,
                   power_consumptionw = ?, lengthm = ?, widthm = ?, heightm = ?, lifespan = ?
             WHERE id = ?",
            'sssss s i d d d d d d i i', // shown spaced for readability, actual string below
            [
                $part->getName(),
                $part->getType(),
                $part->getBrand(),
                $part->getModelNumber(),
                $part->getReleaseDate(),
                $part->getDescription(),
                $part->getPerformanceScore(),
                $part->getMarketPrice(),
                $part->getRsm(),
                $part->getPowerConsumptionW(),
                $part->getLengthM(),
                $part->getWidthM(),
                $part->getHeightM(),
                $part->getLifespan(),
                $part->getId(),
            ]
        );
        // NOTE: PHP ignores whitespace in the types string; use the compact version:
        // 'ssssssidd ddddii' -> to be precise, use:
        // 'ssssssidd ddddii' without spaces => 'ssssssidd ddddii' (compact form below in createOrUpdate)
    }

    public function delete(int $id): bool
    {
        $mysqli = DatabaseManager::getMysqliConnection();
        return $mysqli->prepareAndExecute("DELETE FROM computer_parts WHERE id = ?", 'i', [$id]);
    }

    public function getRandom(): ?ComputerPart
    {
        $mysqli = DatabaseManager::getMysqliConnection();
        $row = $mysqli->prepareAndFetchAll(
            "SELECT * FROM computer_parts ORDER BY RAND() LIMIT 1",
            '',
            []
        )[0] ?? null;

        return $row ? $this->resultToComputerPart($row) : null;
    }

    public function getAll(int $offset, int $limit): array
    {
        $mysqli = DatabaseManager::getMysqliConnection();
        $rows = $mysqli->prepareAndFetchAll(
            "SELECT * FROM computer_parts LIMIT ?, ?",
            'ii',
            [$offset, $limit]
        );

        return $rows ? $this->resultsToComputerParts($rows) : [];
    }

    public function getAllByType(string $type, int $offset, int $limit): array
    {
        $mysqli = DatabaseManager::getMysqliConnection();
        $rows = $mysqli->prepareAndFetchAll(
            "SELECT * FROM computer_parts WHERE type = ? LIMIT ?, ?",
            'sii',
            [$type, $offset, $limit]
        );

        return $rows ? $this->resultsToComputerParts($rows) : [];
    }

    public function createOrUpdate(ComputerPart $partData): bool
    {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query = <<<SQL
        INSERT INTO computer_parts (
            id, name, type, brand, model_number, release_date, description,
            performance_score, market_price, rsm, power_consumptionw,
            lengthm, widthm, heightm, lifespan, submitted_by
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            id = VALUES(id),
            name = VALUES(name),
            type = VALUES(type),
            brand = VALUES(brand),
            model_number = VALUES(model_number),
            release_date = VALUES(release_date),
            description = VALUES(description),
            performance_score = VALUES(performance_score),
            market_price = VALUES(market_price),
            rsm = VALUES(rsm),
            power_consumptionw = VALUES(power_consumptionw),
            lengthm = VALUES(lengthm),
            widthm = VALUES(widthm),
            heightm = VALUES(heightm),
            lifespan = VALUES(lifespan),
            submitted_by = VALUES(submitted_by)
        SQL;

        // Types: i s s s s s s i d d d d d d i i  (16 params)
        $ok = $mysqli->prepareAndExecute(
            $query,
            'issssssidddddddii',
            [
                $partData->getId(),
                $partData->getName(),
                $partData->getType(),
                $partData->getBrand(),
                $partData->getModelNumber(),
                $partData->getReleaseDate(),
                $partData->getDescription(),
                $partData->getPerformanceScore(),
                $partData->getMarketPrice(),
                $partData->getRsm(),
                $partData->getPowerConsumptionW(),
                $partData->getLengthM(),
                $partData->getWidthM(),
                $partData->getHeightM(),
                $partData->getLifespan(),
                $partData->getSubmittedById(),
            ]
        );

        if (!$ok) return false;

        if ($partData->getId() === null) {
            $partData->setId($mysqli->insert_id);
            $ts = $partData->getTimeStamp() ?? new DataTimeStamp(date('Y-m-d H:i:s'), date('Y-m-d H:i:s'));
            $partData->setTimeStamp($ts);
        }

        return true;
    }

    private function resultToComputerPart(array $data): ComputerPart
    {
        return new ComputerPart(
            name: $data['name'],
            type: $data['type'],
            brand: $data['brand'],
            id: isset($data['id']) ? (int)$data['id'] : null,
            modelNumber: $data['model_number'] ?? null,
            releaseDate: $data['release_date'] ?? null,
            description: $data['description'] ?? null,
            performanceScore: isset($data['performance_score']) ? (int)$data['performance_score'] : null,
            marketPrice: isset($data['market_price']) ? (float)$data['market_price'] : null,
            rsm: isset($data['rsm']) ? (float)$data['rsm'] : null,
            powerConsumptionW: isset($data['power_consumptionw']) ? (float)$data['power_consumptionw'] : null,
            lengthM: isset($data['lengthm']) ? (float)$data['lengthm'] : null,
            widthM: isset($data['widthm']) ? (float)$data['widthm'] : null,
            heightM: isset($data['heightm']) ? (float)$data['heightm'] : null,
            lifespan: isset($data['lifespan']) ? (int)$data['lifespan'] : null,
            timeStamp: new DataTimeStamp($data['created_at'] ?? null, $data['updated_at'] ?? null),
            submitted_by_id: isset($data['submitted_by']) ? (int)$data['submitted_by'] : null, // NEW
        );
    }

    private function resultsToComputerParts(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) $out[] = $this->resultToComputerPart($row);
        return $out;
    }

    // Optional legacy name in your interface; delegate to getRandom()
    public function getRandomPart(): ?ComputerPart
    {
        return $this->getRandom();
    }
}

