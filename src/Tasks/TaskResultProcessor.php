<?php declare(strict_types=1);

namespace Reconmap\Tasks;

use Exception;
use Psr\Log\LoggerInterface;
use Reconmap\CommandOutputParsers\Models\Asset;
use Reconmap\CommandOutputParsers\ProcessorFactory;
use Reconmap\Models\Notification;
use Reconmap\Models\Target;
use Reconmap\Repositories\NotificationsRepository;
use Reconmap\Repositories\TargetRepository;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Repositories\VulnerabilityRepository;
use Reconmap\Services\RedisServer;

readonly class TaskResultProcessor implements ItemProcessor
{
    public function __construct(
        private LoggerInterface         $logger,
        private RedisServer             $redis,
        private VulnerabilityRepository $vulnerabilityRepository,
        private NotificationsRepository $notificationsRepository,
        private TaskRepository          $taskRepository,
        private TargetRepository        $targetRepository,
        private ProcessorFactory        $processorFactory)
    {
    }

    public function process(object $item): void
    {
        $path = $item->filePath;

        if (is_null($item->taskId)) {
            return;
        }

        $task = $this->taskRepository->findById($item->taskId);
        $outputParserName = $task['output_parser'];

        $processor = $this->processorFactory->createFromOutputParserName($outputParserName);
        if ($processor) {
            $result = $processor->process($path);
            $hosts = $result->getAssets();
            if (!empty($hosts)) {
                foreach ($hosts as $host) {
                    /** @var Asset $host */
                    $parentTarget = new Target();
                    $parentTarget->project_id = $task['project_id'];
                    $parentTarget->kind = 'hostname';
                    $parentTarget->name = $host->getValue();
                    $parentTarget->tags = json_encode(array_merge($host->getTags(), [$outputParserName]));
                    $parentId = $this->findOrCreateHost($parentTarget);
                    foreach ($host->getChildren() as $childAsset) {
                        $childTarget = new Target();
                        $childTarget->project_id = $task['project_id'];
                        $childTarget->parent_id = $parentId;
                        $childTarget->kind = 'port';
                        $childTarget->name = $childAsset->getValue();
                        $childTarget->tags = json_encode(array_merge($childAsset->getTags(), [$outputParserName]));
                        $this->findOrCreateHost($childTarget);
                    }
                }
                $numHosts = count($hosts);
                $this->logger->debug("Number of hosts in uploaded file: " . $numHosts);

                if ($numHosts > 0) {
                    $notification = new Notification();
                    $notification->toUserId = $item->userId;
                    $notification->title = "New assets found";
                    $notification->content = "A total of '$numHosts' new assets have been found by the '$outputParserName' command";
                    $this->notificationsRepository->insert($notification);
                    $this->redis->lPush("notifications:queue", json_encode(['type' => 'message']));
                }
            }

            $vulnerabilities = $result->getVulnerabilities();
            $numVulnerabilities = count($vulnerabilities);
            $this->logger->debug("Number of vulnerabilities in uploaded file: " . $numVulnerabilities);

            foreach ($vulnerabilities as $vulnerability) {
                try {
                    if (is_array($vulnerability->tags)) {
                        $vulnerability->tags[] = $outputParserName;
                    } else {
                        $vulnerability->tags = [$outputParserName];
                    }

                    $vulnerability->tags = json_encode($vulnerability->tags);
                    $vulnerability->project_id = $task['project_id'];
                    if (empty($vulnerability->risk)) {
                        $vulnerability->risk = 'medium';
                    }
                    $vulnerability->creator_uid = $item->userId;

                    $targetId = null;
                    if (!empty($vulnerability->asset)) {
                        $parentTarget = new Target();
                        $parentTarget->project_id = $task['project_id'];
                        $parentTarget->kind = 'hostname';
                        $parentTarget->name = $vulnerability->asset->getValue();
                        $parentTarget->tags = json_encode(array_merge($vulnerability->asset->getTags(), [$outputParserName]));
                        $targetId = $this->findOrCreateHost($parentTarget);
                    }

                    $vulnerability->target_id = $targetId;

                    $this->vulnerabilityRepository->insert($vulnerability);
                } catch (Exception $e) {
                    $this->logger->error($e->getMessage());
                }
            }

            if ($numVulnerabilities > 0) {
                $notification = new Notification();
                $notification->toUserId = $item->userId;
                $notification->title = "New vulnerabilities found";
                $notification->content = "A total of '$numVulnerabilities' new vulnerabilities have been found by the '$outputParserName' command";
                $this->notificationsRepository->insert($notification);
                $this->redis->lPush("notifications:queue", json_encode(['type' => 'message']));
            }
        } else {
            $this->logger->warning("Task type has no processor: {$task['command_short_name']}");
        }
    }

    private function findOrCreateHost(Target $target): int
    {
        return $this->targetRepository->findOrInsert($target);
    }
}
