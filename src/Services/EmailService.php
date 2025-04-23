<?php declare(strict_types=1);

namespace Reconmap\Services;

use Psr\Log\LoggerInterface;

class EmailService
{
    public function __construct(
        private readonly TemplateEngine    $templateEngine,
        private readonly RedisServer       $redisServer,
        private readonly ApplicationConfig $applicationConfig,
        private readonly LoggerInterface   $logger
    )
    {
    }

    public function queueEmail(string $subject, array $recipients, string $body, ?string $attachmentFilePath = null)
    {
        $queueVars = [
            'subject' => $subject,
            'to' => $recipients,
            'body' => $body
        ];
        if (!is_null($attachmentFilePath)) {
            $queueVars['attachmentPath'] = $attachmentFilePath;
        }

        $result = $this->redisServer->lPush('email:queue',
            json_encode($queueVars)
        );

        if (false === $result) {
            $this->logger->error('Item could not be pushed to the queue', ['queue' => 'email:queue']);
        }
    }

    public function queueTemplatedEmail(string $templatePath, array $templateVars, string $subject, array $recipients)
    {
        $instanceUrl = $this->applicationConfig->getSettings('cors')['allowedOrigins'][0];
        $templateVars['instance_url'] = $instanceUrl;

        $emailBody = $this->templateEngine->render($templatePath, $templateVars);

        $this->queueEmail($subject, $recipients, $emailBody);
    }
}
