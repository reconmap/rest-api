<?php declare(strict_types=1);

namespace Reconmap\Services;

class RedisServer extends \Redis
{
    /**
     * @throws \RedisException
     * @throws \Exception
     */
    public function __construct(Environment $env)
    {
        parent::__construct();

        $host = $env->getValue('REDIS_HOST');
        $port = $env->getValue('REDIS_PORT');

        if (false === $this->connect($host, (int)$port)) {
            throw new \Exception("Unable to connect to Redis server $host:$port");
        }

        $user = $env->getValue('REDIS_USER');
        $password = $env->getValue('REDIS_PASSWORD');

        if (false === $this->auth([$user, $password])) {
            throw new \Exception('Unable to authenticate to Redis');
        }
    }
}
