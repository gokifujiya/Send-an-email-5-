<?php
namespace Database\DataAccess\Implementations;

use Database\DataAccess\Interfaces\ComputerPartDAO;
use Database\DatabaseManager;
use Models\ComputerPart;
use Models\DataTimeStamp;
use Memcached;
use DateTime;

class ComputerPartDAOMemcachedImpl implements ComputerPartDAO
{
    private Memcached $memcached;

    public function __construct()
    {
        $this->memcached = DatabaseManager::getMemcachedConnection();
    }

    private function computerPartToArray(ComputerPart $p): array
    {
        $ts = $p->getTimeStamp();
        return [
            'name'                 => $p->getName(),
            'type'                 => $p->getType(),
            'brand'                => $p->getBrand(),
            'id'                   => $p->getId(),
            'model_number'         => $p->getModelNumber(),
            'release_date'         => $p->getReleaseDate(),
            'description'          => $p->getDescription(),
            'performance_score'    => $p->getPerformanceScore(),
            'market_price'         => $p->getMarketPrice(),
            'rsm'                  => $p->getRsm(),
            'power_consumptionw'   => $p->getPowerConsumptionW(),
            'lengthm'              => $p->getLengthM(),
            'widthm'               => $p->getWidthM(),
            'heightm'              => $p->getHeightM(),
            'lifespan'             => $p->getLifespan(),
            'created_at'           => $ts?->getCreatedAt(),
            'updated_at'           => $ts?->getUpdatedAt(),
        ];
    }

    private function arrayToComputerPart(array $d): ComputerPart
    {
        $ts = (isset($d['created_at']) && isset($d['updated_at']))
            ? new DataTimeStamp($d['created_at'], $d['updated_at'])
            : null;

        return new ComputerPart(
            name: $d['name'] ?? '',
            type: $d['type'] ?? '',
            brand: $d['brand'] ?? '',
            id: $d['id'] ?? null,
            modelNumber: $d['model_number'] ?? null,
            releaseDate: $d['release_date'] ?? null,
            description: $d['description'] ?? null,
            performanceScore: $d['performance_score'] ?? null,
            marketPrice: $d['market_price'] ?? null,
            rsm: $d['rsm'] ?? null,
            powerConsumptionW: $d['power_consumptionw'] ?? null,
            lengthM: $d['lengthm'] ?? null,
            widthM: $d['widthm'] ?? null,
            heightM: $d['heightm'] ?? null,
            lifespan: $d['lifespan'] ?? null,
            timeStamp: $ts
        );
    }

    private function key(int $id): string { return "ComputerPart_$id"; }

    public function create(ComputerPart $partData): bool
    {
        if ($partData->getId() !== null) {
            throw new \Exception('Cannot create with existing ID: ' . $partData->getId());
        }

        // Auto-increment key in cache
        $id = $this->memcached->increment('ComputerPart_auto_id');
        if ($id === false) {
            // initialize sequence
            $this->memcached->set('ComputerPart_auto_id', 1);
            $id = 1;
        }
        $partData->setId((int)$id);

        $now = (new DateTime())->format('Y-m-d H:i:s');
        $ts  = $partData->getTimeStamp() ?? new DataTimeStamp($now, $now);
        $ts->setCreatedAt($ts->getCreatedAt() ?? $now);
        $ts->setUpdatedAt($now);
        $partData->setTimeStamp($ts);

        return $this->memcached->set($this->key($partData->getId()), json_encode($this->computerPartToArray($partData)));
    }

    public function getById(int $id): ?ComputerPart
    {
        $raw = $this->memcached->get($this->key($id));
        if ($raw === false) return null;
        $arr = json_decode($raw, true);
        return $this->arrayToComputerPart($arr);
    }

    public function update(ComputerPart $partData): bool
    {
        if ($partData->getId() === null) {
            throw new \Exception('Cannot update without ID');
        }

        $existing = $this->getById($partData->getId());
        if (!$existing) return false;

        // carry created_at, bump updated_at
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $ts  = $existing->getTimeStamp() ?? new DataTimeStamp($now, $now);
        $ts->setUpdatedAt($now);
        $partData->setTimeStamp($ts);

        return $this->memcached->set($this->key($partData->getId()), json_encode($this->computerPartToArray($partData)));
    }

    public function delete(int $id): bool
    {
        $res = $this->memcached->delete($this->key($id));
        // delete() returns false if key didn't exist; treat as success if key is gone
        return $res || $this->memcached->getResultCode() === Memcached::RES_NOTFOUND;
    }

    public function createOrUpdate(ComputerPart $partData): bool
    {
        return $partData->getId() === null ? $this->create($partData) : $this->update($partData);
    }

    public function getRandom(): ?ComputerPart
    {
        $keys = $this->memcached->getAllKeys();
        if (!is_array($keys)) return null;

        $keys = array_filter($keys, fn($k) => str_starts_with($k, 'ComputerPart_'));
        if (!$keys) return null;

        $randKey = $keys[array_rand($keys)];
        $raw = $this->memcached->get($randKey);
        if ($raw === false) return null;

        return $this->arrayToComputerPart(json_decode($raw, true));
    }

    public function getAll(int $offset, int $limit): array
    {
        $keys = $this->memcached->getAllKeys();
        if (!is_array($keys)) return [];

        $partKeys = array_values(array_filter($keys, fn($k) => str_starts_with($k, 'ComputerPart_')));
        sort($partKeys, SORT_STRING);

        $slice = array_slice($partKeys, $offset, $limit);
        $parts = [];
        foreach ($slice as $k) {
            $raw = $this->memcached->get($k);
            if ($raw === false) continue;
            $parts[] = $this->arrayToComputerPart(json_decode($raw, true));
        }
        return $parts;
    }

    public function getAllByType(string $type, int $offset, int $limit): array
    {
        $all = $this->getAll(0, PHP_INT_MAX);
        $filtered = array_values(array_filter($all, fn(ComputerPart $p) => $p->getType() === $type));
        return array_slice($filtered, $offset, $limit);
    }
}
