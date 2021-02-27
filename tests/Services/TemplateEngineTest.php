<?php declare(strict_types=1);

namespace Reconmap\Services;

use PHPUnit\Framework\TestCase;
use Reconmap\ApplicationConfigTestingTrait;

class TemplateEngineTest extends TestCase
{
    use ApplicationConfigTestingTrait;

    public function testDefaultsAreSet()
    {
        $config = $this->createEmptyApplicationConfig();
        $config->setAppDir('.');

        $engine = new TemplateEngine($config);
        $this->assertEquals('./resources/templates', $engine->getTemplatesDirectory());
    }
}
