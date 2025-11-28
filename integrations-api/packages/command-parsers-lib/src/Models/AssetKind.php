<?php declare(strict_types=1);

namespace Reconmap\CommandOutputParsers\Models;

enum AssetKind: string {
    case CidrRange = 'cidr_range';
    case Hostname = 'hostname';
    case IpAddress = 'ip_address';
    case Port = 'port';
    case Url = 'url';
    case Path = 'path';
    case File = 'file';
}
