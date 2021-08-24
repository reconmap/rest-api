<?php declare(strict_types=1);

namespace Reconmap\Controllers\System;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Services\AttachmentFilePath;

class GetHealthController extends Controller
{
    public function __construct(private AttachmentFilePath $attachmentFilePath)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $attachmentBasePath = $this->attachmentFilePath->generateBasePath();

        return [
            'attachmentsDirectory' => [
                'location' => $attachmentBasePath,
                'exists' => is_dir($attachmentBasePath),
                'writeable' => is_writeable($attachmentBasePath)
            ]
        ];
    }
}
