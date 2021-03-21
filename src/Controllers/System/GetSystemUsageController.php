<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Repositories\AttachmentRepository;

class GetSystemUsageController extends Controller
{
    public function __construct(private AttachmentRepository $attachmentRepository)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        return ['attachments' => $this->attachmentRepository->getUsage()];
    }
}
