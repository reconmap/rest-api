<?php declare(strict_types=1);

namespace Reconmap\Tasks;

use Monolog\Logger;
use Reconmap\Services\ApplicationConfig;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class EmailTaskProcessor implements ItemProcessor
{
    public function __construct(private readonly ApplicationConfig $config,
                                private readonly Logger $logger)
    {
    }

    public function process(object $message): void
    {
        $this->logger->debug('Processing email message');

        $smtpSettings = $this->config->getSettings('smtp');

        $dsn = sprintf('smtp://%s:%s@%s:%d?encryption=tls&auth_mode=login&verify_peer=%s',
            $smtpSettings['username'], $smtpSettings['password'],
            $smtpSettings['host'], $smtpSettings['port'], $smtpSettings['verifyPeer'] ?? true);
        $transport = Transport::fromDsn($dsn);

        $mailer = new Mailer($transport);

        try {
            $email = (new Email())
                ->subject('[Reconmap] ' . $message->subject)
                ->from(new Address($smtpSettings['fromEmail'], $smtpSettings['fromName']))
                ->to($message->to)
                ->text($message->body);

            if (!empty($message->attachmentPath)) {
                $email = $email->attachFromPath($message->attachmentPath);
            }

            try {
                $mailer->send($email);
                $this->logger->debug('Email sent', ['to' => $message->to]);
            } catch (TransportExceptionInterface $e) {
                $this->logger->error('Unable to send email: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
        }
    }
}
