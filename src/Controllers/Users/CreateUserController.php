<?php

declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\AuditLogService;
use Reconmap\Services\Config;
use Reconmap\Services\ConfigConsumer;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class CreateUserController extends Controller implements ConfigConsumer
{
	private $config;

	public function setConfig(Config $config): void
	{
		$this->config = $config;
	}

	public function __invoke(ServerRequestInterface $request, array $args): array
	{
		$requestBody = json_decode((string)$request->getBody());

		$user = $requestBody;
		$user->password = password_hash($requestBody->password, PASSWORD_DEFAULT);

		$repository = new UserRepository($this->db);
		$userId = $repository->create($user);

		$loggedInUserId = $request->getAttribute('userId');

		$this->auditAction($loggedInUserId, $userId);

		if ((bool)($requestBody->sendEmailToUser)) {
			$smtpSettings = $this->config->getSettings('smtp');

			$transport = (new Swift_SmtpTransport($smtpSettings['host'], $smtpSettings['port'], 'tls'))
				->setUsername($smtpSettings['username'])
				->setPassword($smtpSettings['password']);

			$mailer = new Swift_Mailer($transport);

			$emailBody = $this->template->render('users/newAccount', [
				'user' => (array)$user
			]);

			$message = (new Swift_Message('[Reconmap] Account created'))
				->setFrom([$smtpSettings['fromEmail'] => $smtpSettings['fromName']])
				->setTo([$user->email => $user->name])
				->setBody($emailBody);

			if (!$mailer->send($message, $errors)) {
				$this->logger->error('Unable to send email', $errors);
			}
		} else {
			$this->logger->info('Not sending email');
		}

		return (array)$user;
	}

	private function auditAction(int $loggedInUserId, int $userId): void
	{
		$auditLogService = new AuditLogService($this->db);
		$auditLogService->insert($loggedInUserId, AuditLogAction::USER_CREATED, ['type' => 'user', 'id' => $userId]);
	}
}
