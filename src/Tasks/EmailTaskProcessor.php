<?php
declare(strict_types=1);

namespace Reconmap\Tasks;

use Monolog\Logger;
use Reconmap\Services\Config;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class EmailTaskProcessor
{
    private Config $config;
    private Logger $logger;

    public function __construct(Config $config, Logger $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    public function sendMessage(object $message): void
    {
        $smtpSettings = $this->config->getSettings('smtp');

        $transport = (new Swift_SmtpTransport($smtpSettings['host'], $smtpSettings['port'], 'tls'))
            ->setUsername($smtpSettings['username'])
            ->setPassword($smtpSettings['password']);

        $mailer = new Swift_Mailer($transport);

        $email = (new Swift_Message('[Reconmap] ' . $message->subject))
            ->setFrom([$smtpSettings['fromEmail'] => $smtpSettings['fromName']])
            ->setTo([$message->to->email => $message->to->name])
            ->setBody($message->body);

        if (!$mailer->send($email, $errors)) {
            $this->logger->error('Unable to send email', $errors);
        } else {
            $this->logger->debug('Email sent', ['to' => $message->to->email]);
        }
    }
}
