<?php declare(strict_types=1);

namespace Reconmap\Services;

use GuzzleHttp\Client;
use Monolog\Logger;
use Reconmap\Models\User;

readonly class KeycloakService
{
    private array $config;

    public function __construct(private Logger $logger, ApplicationConfig $applicationConfig)
    {
        $this->config = $applicationConfig->getSettings('keycloak');
    }

    private function getClient(): Client
    {
        return new Client([
            'base_uri' => $this->config['baseUri'],
            'timeout' => 2.0,
        ]);
    }

    public function getPublicKey(): string
    {
        $realmInfoEncoded = file_get_contents($this->config['baseUri'] . '/realms/' . $this->config['realmName']);
        $realmInfo = json_decode($realmInfoEncoded);
        $publicKey = $realmInfo->public_key;
        return "-----BEGIN PUBLIC KEY-----\n{$publicKey}\n-----END PUBLIC KEY-----";
    }

    public function getAccessToken(): string
    {
        $client = $this->getClient();
        $response = $client->post('/realms/' . $this->config['realmName'] . '/protocol/openid-connect/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->config['clientId'],
                'client_secret' => $this->config['clientSecret']
            ]]);
        $json = json_decode($response->getBody()->getContents());
        return $json->access_token;
    }

    /**
     * @return string UUID
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createUser(User $user, string $password): string
    {
        $client = $this->getClient();
        list($firstName, $lastName) = explode(' ', $user->full_name);

        $response = $client->post('/admin/realms/' . $this->config['realmName'] . '/users', [
            'headers' => ['Authorization' => 'Bearer ' . $this->getAccessToken()],
            'json' => [
                "firstName" => $firstName,
                "lastName" => $lastName,
                "email" => $user->email,
                "enabled" => "true",
                "username" => $user->username,
                "credentials" => [
                    ["type" => "password", "value" => $password, "temporary" => false]
                ],
                "groups" => [
                    $user->role . "-group"
                ]
            ]
        ]);
        $newUserLocation = $response->getHeaderLine('Location');
        $locationParts = explode('/', $newUserLocation);
        return $locationParts[count($locationParts) - 1];
    }

    public function getUserCredentials(array $user): array
    {
        $client = $this->getClient();

        $response = $client->get('/admin/realms/' . $this->config['realmName'] . '/users/' . $user['subject_id'] . '/credentials', [
            'headers' => ['Authorization' => 'Bearer ' . $this->getAccessToken()]]);
        return json_decode($response->getBody()->getContents(), true);
    }

    public function enableMfa(array $user): bool
    {
        return $this->requireAction($user, 'CONFIGURE_TOTP');
    }

    public function resetPassword(array $user): bool
    {
        return $this->requireAction($user, 'UPDATE_PASSWORD');
    }

    private function requireAction(array $user, string $action): bool
    {
        $client = $this->getClient();

        $response = $client->put('/admin/realms/' . $this->config['realmName'] . '/users/' . $user['subject_id'], [
            'headers' => [
                'Authorization' => "Bearer " . $this->getAccessToken(),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'requiredActions' => [$action],
            ],
        ]);

        return $response->getStatusCode() === 204;
    }

    public function getUser(string $email): void
    {
        $client = $this->getClient();

        $client->get('/admin/realms/' . $this->config['realmName'] . '/users/?email=' . $email, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getAccessToken()
            ]
        ]);
    }

    public function deleteUser(array $user): void
    {
        $client = $this->getClient();

        $client->delete('/admin/realms/' . $this->config['realmName'] . '/users/' . $user['subject_id'], [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getAccessToken()
            ]
        ]);
    }
}
