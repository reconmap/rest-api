<?php declare(strict_types=1);

namespace Reconmap\Controllers\Auth;

use Fig\Http\Message\StatusCodeInterface;
use Firebase\JWT\JWT;
use GuzzleHttp\Psr7\Response;
use PragmaRX\Google2FA\Google2FA;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\Permissions;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\ApplicationConfig;
use Reconmap\Services\AuditLogService;
use Reconmap\Services\JwtPayloadCreator;

class MfaVerificationController extends Controller
{
    public function __construct(
        private Google2FA $google2FA,
        private UserRepository $userRepository,
        private JwtPayloadCreator $jwtPayloadCreator,
        private ApplicationConfig $applicationConfig,
        private AuditLogService $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request): Response
    {
        $userId = $request->getAttribute('userId');

        $user = $this->userRepository->findById($userId);

        $response = new Response;

        $code = $this->getJsonBodyDecoded($request)->code;
        if (!$this->google2FA->verifyKey($user['mfa_secret'], $code)) {
            $this->logger->warning('Invalid 2FA verification code sent for user id: ' . $userId);
            return $response->withStatus(StatusCodeInterface::STATUS_UNAUTHORIZED);
        }
        $this->logger->debug('2fa code valid.');

        $user['mfa'] = 'verified';
        $jwtPayload = $this->jwtPayloadCreator->createFromUserArray($user, false);

        $jwtConfig = $this->applicationConfig->getSettings('jwt');

        $user['access_token'] = JWT::encode($jwtPayload, $jwtConfig['key'], 'HS256');
        $user['permissions'] = Permissions::ByRoles[$user['role']];

        $response->getBody()->write(json_encode($user));
        return $response->withHeader('Content-type', 'application/json');
    }
}
