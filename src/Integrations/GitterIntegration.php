<?php

declare(strict_types=1);

namespace Reconmap\Integrations;

use Reconmap\Services\Config;
use Reconmap\Services\ConfigConsumer;

class GitterIntegration implements Integration, ConfigConsumer, ActivityPublisher
{
    private ?Config $config = null;

    public function setConfig(Config $config): void
    {
        $this->config = $config;
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

    public function publishActivity(string $activity): void
    {
        if (!$this->hasConfiguration()) {
            return;
        }

        $configuration = (object)$this->getConfiguration();

        $client = new \GuzzleHttp\Client(['base_uri' => 'https://api.gitter.im/v1']);
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
