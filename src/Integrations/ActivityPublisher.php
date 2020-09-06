<?php

declare(strict_types=1);

namespace Reconmap\Integrations;

interface ActivityPublisher
{

    public function publishActivity(string $activity): void;
}
