<?php declare(strict_types=1);

namespace Reconmap\Controllers\Clients;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\ClientAuditActions;
use Reconmap\Models\Client;
use Reconmap\Repositories\ClientRepository;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\Filesystem\AttachmentSaver;

class CreateClientController extends Controller
{
    public function __construct(private readonly ClientRepository $repository, private readonly AttachmentSaver $attachmentSaver, private readonly ActivityPublisherService $activityPublisherService)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $files = $request->getUploadedFiles();
        $params = $request->getParsedBody();

        $loggedInUserId = $request->getAttribute('userId');

        $client = new Client();
        $client->name = $params['name'];
        $client->address = $params['address'];
        $client->kind = $params['kind'];
        $client->url = $params['url'];
        $client->creator_uid = $loggedInUserId;
        $client->id = $this->repository->insert($client);

        $logoAttachment = $smallLogoAttachment = null;
        if ($files['logo'])
            $logoAttachment = $this->attachmentSaver->uploadAttachment($files['logo'], 'client', $client->id, $loggedInUserId);

        if ($files['smallLogo'])
            $smallLogoAttachment = $this->attachmentSaver->uploadAttachment($files['smallLogo'], 'client', $client->id, $loggedInUserId);

        if ($logoAttachment || $smallLogoAttachment)
            $this->repository->updateById($client->id, ['logo_attachment_id' => $logoAttachment->id, 'small_logo_attachment_id' => $smallLogoAttachment->id]);

        $this->auditAction($loggedInUserId, $client);

        return $this->createStatusCreatedResponse($client);
    }

    private function auditAction(int $loggedInUserId, Client $client): void
    {
        $this->activityPublisherService->publish($loggedInUserId, ClientAuditActions::CREATED, ['type' => 'client', 'id' => $client->id, 'name' => $client->name]);
    }
}
