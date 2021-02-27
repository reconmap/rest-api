<?php declare(strict_types=1);

namespace Reconmap;

use Noodlehaus\Parser\Json;
use Reconmap\Services\ApplicationConfig;

trait ApplicationConfigTestingTrait
{
    public function createEmptyApplicationConfig(): ApplicationConfig
    {
        return new ApplicationConfig('{}', new Json(), true);
    }
}
