<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\Integrations\GitterIntegration;
use Reconmap\Integrations\Integration;
use Reconmap\Services\ApplicationConfig;

class IntegrationsRepository
{
    private array $integrations;

    public function __construct(ApplicationConfig $config)
    {
        $this->integrations = [
            new GitterIntegration($config)
        ];
    }

    public function findAll(): array
    {
        return array_map(function ($integration) {
            return [
                'name' => $integration->getName(),
                'description' => $integration->getDescription(),
                'configured' => $integration->hasConfiguration(),
                'externalUrl' => $integration->getExternalUrl()
            ];
        }, $this->integrations);
    }

    public function findByInterface(string $classOrInterface): array
    {
        return array_filter($this->integrations, function (Integration $integration) use ($classOrInterface) {
            return $integration instanceof $classOrInterface;
        });
    }
}
