<?php declare(strict_types=1);

namespace Reconmap\Services\Logging;

use Gelf\Publisher;
use Gelf\Transport\IgnoreErrorTransportWrapper;
use Gelf\Transport\UdpTransport;
use Monolog\Handler\GelfHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Reconmap\Services\ApplicationConfig;

class LoggingConfigurator
{
    private const string DEFAULT_LOG_LEVEL = 'info';

    public function __construct(private readonly Logger $logger, private readonly ApplicationConfig $config)
    {
    }

    public function configure(): void
    {
        set_error_handler([$this, 'handleError']);

        $loggingConfig = $this->config->getSettings('logging');

        if ($this->isLoggingHandlerEnabled($loggingConfig, 'file') && isset($loggingConfig['file']['path'])) {
            $level = Level::fromName($loggingConfig['file']['level'] ?? self::DEFAULT_LOG_LEVEL);
            $this->logger->pushHandler(new StreamHandler($loggingConfig['file']['path'], $level));
        }

        if ($this->isLoggingHandlerEnabled($loggingConfig, 'gelf') && isset($loggingConfig['gelf']['serverName'], $loggingConfig['gelf']['serverPort'])) {
            $level = Level::fromName($loggingConfig['gelf']['level'] ?? self::DEFAULT_LOG_LEVEL);
            $transport = new IgnoreErrorTransportWrapper(new UdpTransport($loggingConfig['gelf']['serverName'], $loggingConfig['gelf']['serverPort']));
            $this->logger->pushHandler(new GelfHandler(new Publisher($transport), $level));
        }
    }

    public function handleError(int $errorLevel, string $errorMessage, string $errorFileName, int $errorLineNumber): bool
    {
        if (E_USER_ERROR === $errorLevel) {
            $this->logger->error("$errorMessage on $errorFileName:$errorLineNumber");
        } else {
            $this->logger->warning("$errorMessage on $errorFileName:$errorLineNumber");
        }

        // Don't execute PHP internal error handler
        return true;
    }

    private function isLoggingHandlerEnabled(array $loggingConfig, string $handlerName): bool
    {
        return isset($loggingConfig[$handlerName]) && (!isset($loggingConfig[$handlerName]['enabled']) || $loggingConfig[$handlerName]['enabled']);
    }
}
