<?php
namespace Database\DataAccess;

use Database\DatabaseManager;
use InvalidArgumentException;
use RuntimeException;

abstract class ORM
{
    // Make it a map: class-string => [col => type, ...]
    protected static array $columnTypes = [];
    protected static string $primaryKey = 'id';
    protected array $attributes = [];

    /** Return (and lazily initialize) the column map for this subclass */
    protected static function columnsForThisClass(): array
    {
        $cls = static::class;
        if (!isset(self::$columnTypes[$cls])) {
            // use an instance to call fetchColumnTypes()
            $tmp = new class extends ORM {
                protected static function getTableName(): string { return parent::getTableName(); }
            };
            // but we need the real subclass's columns; instantiate that subclass with no data:
            $real = (new \ReflectionClass($cls))->newInstanceWithoutConstructor();
            // call the protected method on the real instance
            $cols = (function () {
                return $this->fetchColumnTypes();
            })->call($real);
            self::$columnTypes[$cls] = $cols;
        }
        return self::$columnTypes[$cls];
    }

    public function __set($name, $value)
    {
        $cols = static::columnsForThisClass();
        if (array_key_exists($name, $cols) || $name === static::$primaryKey) {
            $this->attributes[$name] = $value;
        }
    }

    public function __get($name) { return $this->attributes[$name] ?? null; }

    public function __construct(array $data = [])
    {
        $cols = static::columnsForThisClass();
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $cols) || $key === static::$primaryKey) {
                $this->attributes[$key] = $value;
            } else {
                throw new InvalidArgumentException(sprintf("%s is not a column of %s", $key, static::class));
            }
        }
    }

    protected static function getTableName(): string
    {
        $classname = strtolower(basename(str_replace('\\', DIRECTORY_SEPARATOR, static::class)));
        $last = $classname[strlen($classname) - 1] ?? '';
        $plural = ($last === 'y' ? 'ies' : ($last === 's' ? 'es' : 's'));
        return $classname . $plural;
    }

    protected function fetchColumnTypes(): array
    {
        $db = DatabaseManager::getMysqliConnection();
        $columnTypes = [];
        $table = static::getTableName();

        $result = $db->query("SHOW COLUMNS FROM {$table}");
        if (!$result) {
            throw new RuntimeException("Failed to fetch columns for table {$table}");
        }

        while ($row = $result->fetch_assoc()) {
            if ($row['Field'] === static::$primaryKey) continue; // only skip PK
            $columnTypes[$row['Field']] = $this->getColumnType($row['Type']);
        }
        return $columnTypes;
    }

    protected function getColumnType(string $type): string
    {
        if (str_contains($type, 'int')) return 'i';
        if (str_contains($type, 'double') || str_contains($type, 'float') || str_contains($type, 'decimal')) return 'd';
        return 's';
    }

    /** ---------- CRUD ---------- */
    public static function create(array $data): static
    {
        $db = DatabaseManager::getMysqliConnection();
        $obj = new static($data);

        $colsMap = static::columnsForThisClass();              // per-class columns
        $cols = implode(', ', array_keys($obj->attributes));
        $ph   = implode(', ', array_fill(0, count($obj->attributes), '?'));
        $stmt = $db->prepare("INSERT INTO " . static::getTableName() . " ({$cols}) VALUES ({$ph})");
        if (!$stmt) throw new RuntimeException("Failed to create row for " . static::class);

        // Build types in the SAME ORDER as values being bound
        $types = '';
        foreach ($obj->attributes as $k => $_) {
            $types .= $colsMap[$k] ?? 's';
        }
        $stmt->bind_param($types, ...array_values($obj->attributes));
        $stmt->execute();

        $obj->__set(static::$primaryKey, $db->insert_id);
        return $obj;
    }

    public static function find(int $id): ?static
    {
        $db = DatabaseManager::getMysqliConnection();
        $table = static::getTableName();
        $pk = static::$primaryKey;

        $stmt = $db->prepare("SELECT * FROM {$table} WHERE {$pk} = ?");
        if (!$stmt) throw new RuntimeException("Failed to find row for " . static::class);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        return $row ? new static($row) : null;
    }

    public function update(array $data): static
    {
        $db = DatabaseManager::getMysqliConnection();
        $table = static::getTableName();
        $pk = static::$primaryKey;

        $colsMap = static::columnsForThisClass();
        $cols = [];
        $vals = [];
        $types = '';
        foreach ($data as $k => $v) {
            if ($k !== $pk && isset($colsMap[$k])) {
                $cols[] = "{$k} = ?";
                $vals[] = $v;
                $types .= $colsMap[$k];
            }
        }

        $sql = "UPDATE {$table} SET " . implode(', ', $cols) . " WHERE {$pk} = ?";
        $stmt = $db->prepare($sql);
        if (!$stmt) throw new RuntimeException("Failed to update " . static::class);

        $types .= 'i';
        $vals[] = $this->id;
        $stmt->bind_param($types, ...$vals);
        $stmt->execute();
        return $this;
    }

    public function delete(): bool
    {
        $db = DatabaseManager::getMysqliConnection();
        $table = static::getTableName();
        $pk = static::$primaryKey;

        if (!isset($this->attributes[$pk])) return false;
        $id = $this->attributes[$pk];

        $stmt = $db->prepare("DELETE FROM {$table} WHERE {$pk} = ?");
        if (!$stmt) throw new RuntimeException("Failed to delete " . static::class);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    /** ---------- NEW: getAll ---------- */
    public static function getAll(?int $limit = null, int $offset = 0): array
    {
        $db = DatabaseManager::getMysqliConnection();
        $table = static::getTableName();

        $sql = "SELECT * FROM {$table}";
        if ($limit !== null) {
            $sql .= " LIMIT ?, ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param('ii', $offset, $limit);
            $stmt->execute();
            $res = $stmt->get_result();
        } else {
            $res = $db->query($sql);
        }

        $out = [];
        while ($row = $res->fetch_assoc()) $out[] = new static($row);
        return $out;
    }

    /** ---------- Relationships ---------- */
    public function hasOne(string $className, ?string $foreignKey = null, ?string $localKey = null): ?object
    {
        $foreignKey ??= strtolower(basename(str_replace('\\', '/', static::class))) . '_id';
        $localKey   ??= static::$primaryKey;

        $table = $className::getTableName();
        $db = DatabaseManager::getMysqliConnection();

        $stmt = $db->prepare("SELECT * FROM {$table} WHERE {$foreignKey} = ? LIMIT 1");
        $id = $this->$localKey;
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();

        return $row ? new $className($row) : null;
    }

    public function belongsTo(string $className, ?string $foreignKey = null, ?string $ownerKey = null): ?object
    {
        $foreignKey ??= strtolower(basename(str_replace('\\', '/', $className))) . '_id';

        // read target PK via reflection (defaults to 'id')
        $ownerKey ??= (function () { return static::$primaryKey; })->call(new $className());

        $fkVal = $this->$foreignKey;
        if ($fkVal === null) return null;

        return $className::find((int)$fkVal);
    }
}
