<?php declare(strict_types=1);

namespace Reconmap\Controllers\Commands;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\CommandOutput;
use Reconmap\Repositories\CommandOutputRepository;
use Reconmap\Services\RedisServer;

class UploadCommandOutputController extends Controller
{

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $params = $request->getParsedBody();
        $taskId = (int)$params['taskId'];

        $pathName = RECONMAP_APP_DIR . '/data/task-results/' . uniqid(gethostname());

        $files = $request->getUploadedFiles();

        $resultFile = $files['resultFile'];
        if ($resultFile->getError() === UPLOAD_ERR_OK) {
            $resultFile->moveTo($pathName);
        }

        $userId = $request->getAttribute('userId');

        $commandOutput = new CommandOutput();
        $commandOutput->command_id = 2;
        $commandOutput->submitted_by_uid = $userId;
        $commandOutput->file_name = $resultFile->getClientFilename();
        $commandOutput->file_content = file_get_contents($pathName);
        $commandOutput->file_size = filesize($pathName);
        $commandOutput->file_mimetype = mime_content_type($pathName);

        $repository = new CommandOutputRepository($this->db);
        $repository->insert($commandOutput);

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
