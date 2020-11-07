<?php
declare(strict_types=1);

namespace Reconmap\Services;

use League\Plates\Engine;

class TemplateEngine extends Engine
{
    public function __construct(Config $config)
    {
        parent::__construct($config->getAppDir() . '/resources/templates');
    }
}
