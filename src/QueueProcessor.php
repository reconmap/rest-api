<?php
declare(strict_types=1);

namespace Reconmap;

use Monolog\Logger;
use Reconmap\Services\ApplicationConfig;
use Reconmap\Services\RedisServer;
use Reconmap\Tasks\ItemProcessor;

class QueueProcessor
{
    private RedisServer $redis;
    private ApplicationConfig $config;
    private Logger $logger;

    public function __construct(RedisServer $redis, ApplicationConfig $config, Logger $logger)
    {
        $this->redis = $redis;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function run(ItemProcessor $itemProcessor, string $queueName): int
    {
        $processorClass = get_class($itemProcessor);
        $this->logger->debug("Running queue processor", ['class' => $processorClass]);

        while ($itemEncoded = $this->redis->brPop($queueName, 1)) {
            $this->logger->debug('Pulling item from queue', ['item' => $itemEncoded]);
            $item = json_decode($itemEncoded[1]);
            $this->logger->debug('Item decoded', [$item]);
            $itemProcessor->process($item);
        }

        return ExitCode::EXIT_SUCCESS;
    }
}
