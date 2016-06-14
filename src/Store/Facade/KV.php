<?php
/**
 * Created by IntelliJ IDEA.
 * User: Demon
 * Date: 16/5/4
 * Time: 下午6:35
 */

namespace Zan\Framework\Store\Facade;


use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Connection\ConnectionManager;
use Zan\Framework\Store\NoSQL\Exception;
use Zan\Framework\Store\NoSQL\KV\KVStore;

class KV
{
    const POOL_PREFIX = 'connection.kvstore.';
    private $namespace;
    private $setName;

    private static $_instance = null;


    /**
     * @param $namespace
     * @param $setName
     * @return mixed
     */
    private static function init($namespace, $setName)
    {
        if (null === self::$_instance[$namespace]) {
            self::$_instance[$namespace] = new KV($namespace, $setName);
        }
        return self::$_instance[$namespace];
    }

    /**
     * KV constructor.
     * @param $namespace
     * @param $setName
     */
    private function __construct($namespace, $setName)
    {
        $this->namespace = $namespace;
        $this->setName = $setName;
    }

    /**
     * @param $config
     * @param $key
     * @param $value
     * @param int $ttl
     * @return \Generator|void
     * @throws Exception
     */
    public static function set($config, $key, $value, $ttl = 0)
    {
        $config = Config::get('kvstore.' . $config);
        if (!self::validConfig($config)) {
            yield false;
            return;
        }
        $kvObj = self::init($config['namespace'], $config['set']);
        $conn = (yield $kvObj->getConnection($config['connection']));
        $kv = new KVStore($kvObj->namespace, $kvObj->setName, $conn);
        yield $kv->set($key, $value, $ttl);
    }

    /**
     * @param $config
     * @param $key
     * @param array $value
     * @param int $ttl
     * @return \Generator|void
     */
    public static function setList($config, $key, array $value, $ttl = 0)
    {
        $config = Config::get('kvstore.' . $config);
        if (!self::validConfig($config)) {
            yield false;
            return;
        }
        $kvObj = self::init($config['namespace'], $config['set']);
        $conn = (yield $kvObj->getConnection($config['connection']));
        $kv = new KVStore($kvObj->namespace, $kvObj->setName, $conn);
        yield $kv->setList($key, $value, $ttl);
    }

    /**
     * @param $config
     * @param $key
     * @param array $value
     * @param int $ttl
     * @return \Generator|void
     */
    public static function setMap($config, $key, array $value, $ttl = 0)
    {
        $config = Config::get('kvstore.' . $config);
        if (!self::validConfig($config)) {
            yield false;
            return;
        }
        $kvObj = self::init($config['namespace'], $config['set']);
        $conn = (yield $kvObj->getConnection($config['connection']));
        $kv = new KVStore($kvObj->namespace, $kvObj->setName, $conn);
        yield $kv->setMap($key, $value, $ttl);
    }

    public static function incr($config, $key, $value = 1)
    {
        $config = Config::get('kvstore.' . $config);
        if (!self::validConfig($config)) {
            yield false;
            return;
        }
        $kvObj = self::init($config['namespace'], $config['set']);
        $conn = (yield $kvObj->getConnection($config['connection']));
        $kv = new KVStore($kvObj->namespace, $kvObj->setName, $conn);
        yield $kv->incr($key, $value);
    }

    /**
     * @param $key
     * @param string $config
     * @return \Generator|void
     */
    public static function get($config, $key)
    {
        $config = Config::get('kvstore.' . $config);
        if (!self::validConfig($config)) {
            yield false;
            return;
        }
        $kvObj = self::init($config['namespace'], $config['set']);
        $conn = (yield $kvObj->getConnection($config['connection']));
        $kv = new KVStore($kvObj->namespace, $kvObj->setName, $conn);
        yield $kv->get($key);
    }

    /**
     * @param $key
     * @return \Generator
     * @throws Exception
     */
    public static function remove($config, $key)
    {
        $config = Config::get('kvstore.' . $config);
        if (!self::validConfig($config)) {
            yield false;
            return;
        }
        $kvObj = self::init($config['namespace'], $config['set']);
        $conn = (yield $kvObj->getConnection($config['connection']));
        $kv = new KVStore($kvObj->namespace, $kvObj->setName, $conn);
        yield $kv->remove($key);
    }

    /**
     * @param $connection
     * @return \Generator
     * @throws Exception
     * @throws \Zan\Framework\Foundation\Exception\System\InvalidArgumentException
     */
    public function getConnection($connection)
    {
        $conn = (yield ConnectionManager::getInstance()->get($connection));
        if (!$conn instanceof Connection) {
            throw new Exception('KV get connection error');
        }

        yield $conn;
    }

    /**
     * @param $config
     * @return bool
     */
    private static function validConfig($config)
    {
        if (!$config) {
            return false;
        }

        if (!isset($config['connection'])
            || !isset($config['namespace'])
            || !isset($config['set'])) {
            return false;
        }

        return true;
    }
}