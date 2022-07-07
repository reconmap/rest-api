<?php declare(strict_types=1);

namespace Reconmap\Services;

use GuzzleHttp\Client;
use Reconmap\Models\User;

class KeycloakService
{
    private readonly array $config;

    public function __construct(ApplicationConfig $config)
    {
        $this->config = $config->getSettings('keycloak');
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
        $client = new Client([
            'base_uri' => $this->config['baseUri'],
            'timeout' => 2.0,
        ]);
        $response = $client->post('/realms/reconmap/protocol/openid-connect/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => 'admin-cli',
                'client_secret' => $this->config['clientSecret']
            ]]);
        $json = json_decode($response->getBody()->getContents());
        return $json->access_token;
    }

    public function createUser(User $user, string $accessToken)
    {
        $client = new Client([
            'base_uri' => $this->config['baseUri'],
            'timeout' => 2.0,
        ]);
        list($firstName, $lastName) = explode(' ', $user->full_name);

        $client->post('/admin/realms/reconmap/users', [
            'headers' => ['Authorization' => 'Bearer ' . $accessToken],
            'json' => [
                "firstName" => $firstName,
                "lastName" => $lastName,
                "email" => $user->email,
                "enabled" => "true",
                "username" => $user->username
            ]
        ]);
    }

    public function getUser(string $email)
    {
        $client = new Client([
            'base_uri' => $this->config['baseUri'],
            'timeout' => 2.0,
        ]);

        $client->get('/admin/realms/reconmap/users/?email=' . $email, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getAccessToken()
            ]
        ]);
    }

    public function deleteUser(User $user)
    {
        $client = new Client([
            'base_uri' => $this->config['baseUri'],
            'timeout' => 2.0,
        ]);

        $client->delete('/admin/realms/reconmap/users/' . $user->oidc_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getAccessToken()
            ]
        ]);
    }
}

