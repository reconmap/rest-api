<?php
declare(strict_types=1);

namespace Reconmap\Tasks;

use Monolog\Logger;
use Reconmap\Services\ApplicationConfig;
use Swift_Attachment;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class EmailTaskProcessor implements ItemProcessor
{
    private ApplicationConfig $config;
    private Logger $logger;

    public function __construct(ApplicationConfig $config, Logger $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    public function process(object $message): void
    {
        $this->logger->debug('Processing email message');

        $smtpSettings = $this->config->getSettings('smtp');

        $transport = (new Swift_SmtpTransport($smtpSettings['host'], $smtpSettings['port'], 'tls'))
            ->setUsername($smtpSettings['username'])
            ->setPassword($smtpSettings['password']);

        $mailer = new Swift_Mailer($transport);

        try {
            $email = (new Swift_Message('[Reconmap] ' . $message->subject))
                ->setFrom([$smtpSettings['fromEmail'] => $smtpSettings['fromName']])
                ->setTo($message->to)
                ->setBody($message->body);

            if (!empty($message->attachmentPath)) {
                $attachment = Swift_Attachment::fromPath($message->attachmentPath);
                $email->attach($attachment);
            }

            if (!$mailer->send($email, $errors)) {
                $this->logger->error('Unable to send email', $errors);
            } else {
                $this->logger->debug('Email sent', ['to' => $message->to]);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
        }
    }
}
