<?php
namespace Database;

use Helpers\Settings;
use Memcached;

class DatabaseManager
{
    protected static array $mysqliConnections   = [];
    protected static array $memcachedConnections = [];

    public static function getMysqliConnection(string $connectionName = 'default'): MySQLWrapper
    {
        if (!isset(static::$mysqliConnections[$connectionName])) {
            static::$mysqliConnections[$connectionName] = new MySQLWrapper();
        }
        return static::$mysqliConnections[$connectionName];
    }

    public static function getMemcachedConnection(string $connectionName = 'default'): Memcached
    {
        if (!isset(static::$memcachedConnections[$connectionName])) {
            $host = Settings::env('MEMCACHED_HOST', '127.0.0.1');
            $port = (int) (Settings::env('MEMCACHED_PORT', '11211') ?? 11211);

            $m = new Memcached();
            $m->addServer($host, $port);

            static::$memcachedConnections[$connectionName] = $m;
        }
        return static::$memcachedConnections[$connectionName];
    }
}

