<?php declare(strict_types=1);

namespace Reconmap\Models;

class Report
{
    public ?int $id = null;
    public int $projectId;
    public ?int $generatedByUid = null;
    public ?string $insertTs = null;
    public ?string $versionName = null;
    public ?string $versionDescription = null;
}
