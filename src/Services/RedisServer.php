<?php declare(strict_types=1);

namespace Reconmap\Services;

class RedisServer extends \Redis
{
    /**
     * @throws \RedisException
     * @throws \Exception
     */
    public function __construct(ApplicationConfig $config)
    {
        parent::__construct();

        $redisConfig = $config->getSettings('redis');
        $host = $redisConfig['host'];
        $port = intval($redisConfig['port']);

        if (false === $this->connect($host, $port)) {
            throw new \Exception("Unable to connect to Redis server $host:$port");
        }

        $username = $redisConfig['username'];
        $password = $redisConfig['password'];

        if (false === $this->auth([$username, $password])) {
            throw new \Exception('Unable to authenticate to Redis');
        }
    }
}
