<?php declare(strict_types=1);

namespace Reconmap;

use Reconmap\Services\ApplicationConfig;

trait ApplicationConfigTestingTrait
{
    public function createEmptyApplicationConfig(): ApplicationConfig
    {
        return new ApplicationConfig();
    }
}
