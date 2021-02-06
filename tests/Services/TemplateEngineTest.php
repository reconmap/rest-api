<?php declare(strict_types=1);

namespace Reconmap\Services;

use PHPUnit\Framework\TestCase;

class TemplateEngineTest extends TestCase
{
    public function testDefaultsAreSet()
    {
        $config = new Config();
        $config->update('appDir', '.');

        $engine = new TemplateEngine($config);
        $this->assertEquals('./resources/templates', $engine->getTemplatesDirectory());
    }
}
