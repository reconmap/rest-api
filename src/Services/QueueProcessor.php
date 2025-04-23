<?php declare(strict_types=1);

namespace Reconmap\Services;

use Psr\Log\LoggerInterface;
use Reconmap\ExitCode;
use Reconmap\Tasks\ItemProcessor;

readonly class QueueProcessor
{
    public function __construct(private RedisServer     $redis,
                                private LoggerInterface $logger)
    {
    }

    public function run(ItemProcessor $itemProcessor, string $queueName): int
    {
        while ($itemEncoded = $this->redis->brPop($queueName, 1)) {
            $this->logger->debug('Pulling item from queue', ['queueName' => $queueName]);
            $item = json_decode($itemEncoded[1]);
            $itemProcessor->process($item);
        }

        return ExitCode::SUCCESS->value;
    }
}
