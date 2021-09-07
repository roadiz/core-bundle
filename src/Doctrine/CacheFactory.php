<?php
declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine;

use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\Common\Cache\RedisCache;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\Cache\Adapter\PhpArrayAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class CacheFactory
{
    /**
     * Get cache driver according to config.yml entry.
     *
     * Logic from Doctrine setup method
     * https://github.com/doctrine/doctrine2/blob/master/lib/Doctrine/ORM/Tools/Setup.php#L122
     *
     * @param array $cacheConfig
     * @param string $environment
     * @param string $cacheDir
     * @param string $namespace
     *
     * @return CacheProvider
     */
    public static function fromConfig(
        array $cacheConfig,
        string $environment,
        string $cacheDir,
        string $namespace = 'dc2'
    ): CacheProvider {
        if (empty($cacheConfig['type'])) {
            $cache = new ArrayCache();
        } elseif (extension_loaded('apcu') &&
            !empty($cacheConfig['type']) &&
            $cacheConfig['type'] == 'apcu'
        ) {
            $cache = new ApcuCache();
        } elseif (extension_loaded('apc') &&
            !empty($cacheConfig['type']) &&
            $cacheConfig['type'] == 'apc'
        ) {
            $cache = new ApcuCache();
        } elseif (!empty($cacheConfig['type']) &&
            $cacheConfig['type'] == 'php'
        ) {
            $cache = new PhpFileCache($cacheDir.'/doctrine');
        } elseif (!empty($cacheConfig['type']) &&
            $cacheConfig['type'] == 'file'
        ) {
            $cache = new FilesystemCache($cacheDir.'/doctrine');
        } elseif (extension_loaded('memcached') &&
            class_exists('\Memcached') &&
            !empty($cacheConfig['type']) &&
            $cacheConfig['type'] == 'memcached'
        ) {
            $memcached = new \Memcached();
            $host = !empty($cacheConfig['host']) ? $cacheConfig['host'] : '127.0.0.1';
            $port = !empty($cacheConfig['port']) ? $cacheConfig['port'] : 11211;
            $memcached->addServer($host, $port);

            $cache = new MemcachedCache();
            $cache->setMemcached($memcached);
        } elseif (extension_loaded('redis') &&
            class_exists('\Redis') &&
            !empty($cacheConfig['type']) &&
            $cacheConfig['type'] == 'redis'
        ) {
            $redis = new \Redis();
            $host = !empty($cacheConfig['host']) ? $cacheConfig['host'] : '127.0.0.1';
            if (!empty($cacheConfig['port'])) {
                $redis->connect($host, $cacheConfig['port']);
            } else {
                $redis->connect($host);
            }
            $cache = new RedisCache();
            $cache->setRedis($redis);
        } else {
            $cache = new ArrayCache();
        }
        $cache->setNamespace(static::getNamespace($namespace, false, $environment));
        return $cache;
    }

    /**
     * @param array $cacheConfig
     * @param string $environment
     * @param string $cacheDir
     * @param string $namespace
     * @return CacheItemPoolInterface
     * @throws \Symfony\Component\Cache\Exception\CacheException
     */
    public static function psrCacheFromConfig(
        array $cacheConfig,
        string $environment,
        string $cacheDir,
        string $namespace = 'dc2'
    ): CacheItemPoolInterface {
        $namespace = static::getNamespace($namespace, false, $environment);
        if (empty($cacheConfig['type'])) {
            return new ArrayAdapter();
        } elseif (extension_loaded('apcu') &&
            !empty($cacheConfig['type']) &&
            $cacheConfig['type'] == 'apcu'
        ) {
            return new ApcuAdapter($namespace);
        } elseif (!empty($cacheConfig['type']) &&
            $cacheConfig['type'] == 'php'
        ) {
            return new PhpArrayAdapter($cacheDir.'/cache_pool.php', new ArrayAdapter());
        } elseif (!empty($cacheConfig['type']) &&
            $cacheConfig['type'] == 'file'
        ) {
            return new FilesystemAdapter($namespace,0,$cacheDir.'/adapter');
        } elseif (extension_loaded('memcached') &&
            class_exists('\Memcached') &&
            !empty($cacheConfig['type']) &&
            $cacheConfig['type'] == 'memcached'
        ) {
            $memcached = new \Memcached();
            $host = !empty($cacheConfig['host']) ? $cacheConfig['host'] : '127.0.0.1';
            $port = !empty($cacheConfig['port']) ? $cacheConfig['port'] : 11211;
            $memcached->addServer($host, $port);
            return new MemcachedAdapter($memcached, $namespace);
        } elseif (extension_loaded('redis') &&
            class_exists('\Redis') &&
            !empty($cacheConfig['type']) &&
            $cacheConfig['type'] == 'redis'
        ) {
            $redis = new \Redis();
            $host = !empty($cacheConfig['host']) ? $cacheConfig['host'] : '127.0.0.1';
            if (!empty($cacheConfig['port'])) {
                $redis->connect($host, $cacheConfig['port']);
            } else {
                $redis->connect($host);
            }
            return new RedisAdapter($redis, $namespace);
        }
        return new ArrayAdapter();
    }

    /**
     * @param string $namespace
     * @param bool $isPreview
     * @param string $environment
     * @return string
     */
    public static function getNamespace(
        string $namespace = 'dc2',
        bool $isPreview = false,
        string $environment = 'prod'
    ): string {
        $namespace = $namespace . "_" . $environment . "_";
        if ($isPreview) {
            $namespace .= 'preview_';
        }

        return $namespace;
    }
}
