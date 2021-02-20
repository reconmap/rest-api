<?php declare(strict_types=1);

namespace Reconmap;

use League\Route\Strategy\JsonStrategy;
use Psr\Http\Message\ResponseInterface;
use Reconmap\Services\Config;

class ApiStrategy extends JsonStrategy
{
    private Config $config;

    public function setConfig(Config $config): void
    {
        $this->config = $config;
    }

    public function getOptionsCallable(array $methods): callable
    {
        $corsConfig = $this->config->getSettings('cors');
        $allowedOrigins = implode(',', $corsConfig['allowedOrigins']);

        $parentOptionsCallable = parent::getOptionsCallable($methods);
        return function () use ($parentOptionsCallable, $allowedOrigins, $methods): ResponseInterface {
            return $parentOptionsCallable()
                ->withHeader('Access-Control-Allow-Headers', 'Authorization,Bulk-Operation,Content-Type')
                ->withHeader('Access-Control-Allow-Origin', $allowedOrigins);
        };
    }
}
