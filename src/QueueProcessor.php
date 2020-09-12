<?php
declare(strict_types=1);

namespace Reconmap;

use Monolog\Logger;
use Reconmap\Services\Config;
use Reconmap\Tasks\EmailTaskProcessor;
use Redis;

class QueueProcessor
{
    private Redis $redis;
    private Config $config;
    private Logger $logger;

    public function __construct(Redis $redis, Config $config, Logger $logger)
    {
        $this->redis = $redis;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function run(): int
    {
        $this->logger->debug('Running queue processor');
        $emailTaskProcessor = new EmailTaskProcessor($this->config, $this->logger);

        while ($item = $this->redis->brPop('email:queue', 1)) {
            $this->logger->debug('Pulling item from queue', ['item' => $item]);
            $message = json_decode($item[1]);
            $emailTaskProcessor->sendMessage($message);
        }

        return ExitCode::EXIT_SUCCESS;
    }
}
