<?php declare(strict_types=1);

namespace Reconmap\Services;

use GuzzleHttp\Client;
use Monolog\Logger;
use Reconmap\Models\User;

class KeycloakService
{
    private readonly array $config;

    public function __construct(private readonly Logger $logger, ApplicationConfig $config)
    {
        $this->config = $config->getSettings('keycloak');
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
        $realmInfoEncoded = file_get_contents($this->config['baseUri'] . '/realms/reconmap');
        $realmInfo = json_decode($realmInfoEncoded);
        $publicKey = $realmInfo->public_key;
        return "-----BEGIN PUBLIC KEY-----\n{$publicKey}\n-----END PUBLIC KEY-----";
    }

    public function getAccessToken(): string
    {
        $client = $this->getClient();
        $response = $client->post('/realms/reconmap/protocol/openid-connect/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => 'admin-cli',
                'client_secret' => $this->config['clientSecret']
            ]]);
        $json = json_decode($response->getBody()->getContents());
        return $json->access_token;
    }

    /**
     * @param User $user
     * @param string $accessToken
     * @return string UUID
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createUser(User $user, string $accessToken): string
    {
        $client = $this->getClient();
        list($firstName, $lastName) = explode(' ', $user->full_name);

        $response = $client->post('/admin/realms/reconmap/users', [
            'headers' => ['Authorization' => 'Bearer ' . $accessToken],
            'json' => [
                "firstName" => $firstName,
                "lastName" => $lastName,
                "email" => $user->email,
                "enabled" => "true",
                "username" => $user->username
            ]
        ]);
        $newUserLocation = $response->getHeaderLine('Location');
        $locationParts = explode('/', $newUserLocation);
        return $locationParts[count($locationParts) - 1];
    }

    public function getUser(string $email)
    {
        $client = $this->getClient();

        $client->get('/admin/realms/reconmap/users/?email=' . $email, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getAccessToken()
            ]
        ]);
    }

    public function deleteUser(User $user)
    {
        $client = $this->getClient();

        $client->delete('/admin/realms/reconmap/users/' . $user->subject_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getAccessToken()
            ]
        ]);
    }
}

