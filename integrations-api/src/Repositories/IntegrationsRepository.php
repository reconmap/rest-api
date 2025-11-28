<?php declare(strict_types=1);

namespace Reconmap\Repositories;

use Reconmap\CommandOutputParsers\ProcessorFactory;
use Reconmap\Integrations\GitterIntegration;
use Reconmap\Integrations\Integration;
use Reconmap\Services\ApplicationConfig;

class IntegrationsRepository
{
    private array $integrations;

    public function __construct(ApplicationConfig $config, ProcessorFactory $processorFactory)
    {
        $this->integrations = [
            new GitterIntegration($config)
        ];
        foreach ($processorFactory->getAll() as $parser) {
            $this->integrations[] = new class($parser['name']) implements Integration {

                public function __construct(private readonly string $parserName)
                {
                }

                public function getName(): string
                {
                    return $this->parserName;
                }

                public function getDescription(): string
                {
                    return '-';
                }

                public function getConfiguration(): array
                {
                    return [];
                }

                public function hasConfiguration(): bool
                {
                    return true;
                }

                public function getExternalUrl(): string
                {
                    return 'https://github.com/reconmap/rest-api/tree/master/packages/command-parsers-lib';
                }
            };
        }
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
