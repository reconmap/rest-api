<?php

declare(strict_types=1);

namespace Reconmap\Repositories;

use League\Container\Container;
use Reconmap\Integrations\GitterIntegration;
use Reconmap\Integrations\Integration;

class IntegrationsRepository
{
    private array $integrations;

    public function __construct(Container $container)
    {
        $this->integrations = [
            $container->get(GitterIntegration::class)
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
