<?php declare(strict_types=1);

namespace Reconmap\Integrations;

use GuzzleHttp\Client;
use Reconmap\Services\ApplicationConfig;

class GitterIntegration implements Integration, ActivityPublisher
{
    public function __construct(private readonly ApplicationConfig $config)
    {
    }

    public function getName(): string
    {
        return 'Gitter';
    }

    public function getDescription(): string
    {
        return 'Post activity messages to a Gitter room';
    }

    public function getExternalUrl(): string
    {
        return 'https://gitter.im';
    }

    public function publishActivity(\BackedEnum $action): void
    {
        if (!$this->hasConfiguration()) {
            return;
        }

        $activity = $action->value;

        $configuration = (object)$this->getConfiguration();

        if (!$configuration->enabled) {
            return;
        }

        $client = new Client(['base_uri' => 'https://api.gitter.im/v1']);
        $client->request('POST', '/v1/rooms/' . $configuration->roomId . '/chatMessages', [
            'headers' => ['Authorization' => 'Bearer ' . $configuration->token],
            'json' => [
                'text' => "New event on Reconmap: $activity",
                'html' => "<strong>New event on Reconmap:</strong> $activity"
            ]
        ]);
    }

    public function hasConfiguration(): bool
    {
        $integrationsConfig = $this->config->getSettings('integrations');
        return isset($integrationsConfig['gitter']);
    }

    public function getConfiguration(): array
    {
        $integrationsConfig = $this->config->getSettings('integrations');
        return $integrationsConfig['gitter'];
    }
}
