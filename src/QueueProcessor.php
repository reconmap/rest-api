<?php declare(strict_types=1);

namespace Reconmap;

use Monolog\Logger;
use Reconmap\Services\RedisServer;
use Reconmap\Tasks\ItemProcessor;

class QueueProcessor
{
    public function __construct(private RedisServer $redis,
                                private Logger $logger)
    {
    }

    public function run(ItemProcessor $itemProcessor, string $queueName): int
    {
        while ($itemEncoded = $this->redis->brPop($queueName, 1)) {
            $this->logger->debug('Pulling item from queue', ['item' => $itemEncoded]);
            $item = json_decode($itemEncoded[1]);
            $this->logger->debug('Item decoded', [$item]);
            $itemProcessor->process($item);
        }

        return ExitCode::EXIT_SUCCESS;
    }
}
