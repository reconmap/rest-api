<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Tasks;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Services\RedisServer;

class UploadTaskResultController extends Controller
{

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $params = $request->getParsedBody();
        $taskId = (int)$params['taskId'];

        $pathName = RECONMAP_APP_DIR . '/data/task-results/' . uniqid(gethostname());

        $files = $request->getUploadedFiles();

        $resultFile = $files['resultFile'];
        $resultFile->moveTo($pathName);

        $userId = $request->getAttribute('userId');

        /** @var RedisServer $redis */
        $redis = $this->container->get(RedisServer::class);
        $result = $redis->lPush("tasks:queue",
            json_encode([
                'taskId' => $taskId,
                'userId' => $userId,
                'filePath' => $pathName
            ])
        );
        if (false === $result) {
            $this->logger->error('Item could not be pushed to the queue', ['queue' => 'tasks-results:queue']);
        }

        return ['success' => true];
    }
}
