<?php

declare(strict_types=1);

namespace Reconmap\Tasks;

use LLPhant\Chat\OllamaChat;
use LLPhant\OllamaConfig;
use Psr\Log\LoggerInterface;
use Reconmap\Models\Notification;
use Reconmap\Repositories\NotificationsRepository;
use Reconmap\Repositories\VulnerabilityRepository;
use Reconmap\Services\ApplicationConfig;
use Reconmap\Services\RedisServer;

readonly class AiTaskProcessor implements ItemProcessor
{
    public function __construct(
        private ApplicationConfig $config,
        private LoggerInterface   $logger,
        private readonly VulnerabilityRepository  $repository,
        private readonly NotificationsRepository  $notificationsRepository,
        private readonly RedisServer $redisServer,
    ) {}

    public function process(object $message): void
    {
        $this->logger->debug('Processing LLM job');

        try {
            $appConfig = $this->config->getSettings('integrations');

            $config = new OllamaConfig();
            $config->url = $appConfig['ollama']['url'];
            $config->model = $appConfig['ollama']['model'];
            $config->stream = false;

            $vulnerability = $this->repository->findById($message->vulnerabilityId);
            $this->logger->debug($vulnerability['summary']);
            $chat = new OllamaChat($config);
            $chat->setSystemMessage('You are a vulnerability and pentesting expert system');
            $remediation = $chat->generateText('Write instructions on how to remediate this vulnerability: ' . $vulnerability['summary']);

            $success = $this->repository->updateById($message->vulnerabilityId, ['remediation' => $remediation]);

            $notification = new Notification();
            $notification->toUserId = $message->loggedInUserId;
            $notification->title = "AI job completed";
            $notification->content = "The vulnerability remediation instructions have now been generated";
            $this->notificationsRepository->insert($notification);
            $this->redisServer->lPush("notifications:queue", json_encode(['type' => 'message']));
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
