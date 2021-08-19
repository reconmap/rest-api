<?php declare(strict_types=1);

namespace Reconmap\Processors;

class SubfinderOutputProcessor extends AbstractCommandParser implements HostParser
{

    public function parseHost(string $path): array
    {
        $hosts = [];

        $lines = file($path);
        foreach ($lines as $line) {
            /*
             {
{"host":"mail.rmap.org","source":"crtsh"}
{"host":"www.rmap.org","source":"crtsh"}

            }
             */
            $json = json_decode($line);
            $host = [
                'name' => $json->host
            ];

            $hosts[] = (object)$host;
        }

        return $hosts;
    }
}
