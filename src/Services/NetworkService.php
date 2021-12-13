<?php declare(strict_types=1);

namespace Reconmap\Services;

class NetworkService
{
    private array $serverVars;

    public function __construct(array $serverVars = null)
    {
        $this->serverVars = $serverVars ?? $_SERVER;
    }

    public function getClientIp(): string
    {
        if (!empty($this->serverVars['HTTP_CLIENT_IP'])) {
            $ip = $this->serverVars['HTTP_CLIENT_IP'];
        } elseif (!empty($this->serverVars['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $this->serverVars['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } else {
            $ip = $this->serverVars['REMOTE_ADDR'];
        }
        return $ip;
    }
}
