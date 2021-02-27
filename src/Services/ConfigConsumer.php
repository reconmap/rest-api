<?php
declare(strict_types=1);

namespace Reconmap\Services;

interface ConfigConsumer
{

    public function setConfig(ApplicationConfig $config): void;
}
