<?php declare(strict_types=1);

namespace Reconmap\Controllers\Users;

use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditLogAction;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\ActivityPublisherService;
use Reconmap\Services\EmailService;

class UpdateUserController extends Controller
{
    public function __construct(
        private UserRepository $userRepository,
        private EmailService $emailService,
        private ActivityPublisherService $activityPublisherService)
    {
    }

    public function __invoke(ServerRequestInterface $request, array $args): array
    {
        $userId = (int)$args['userId'];

        $requestBody = $this->getJsonBodyDecodedAsArray($request);
        $newColumnValues = array_filter(
            $requestBody,
            fn(string $key) => in_array($key, array_keys(UserRepository::UPDATABLE_COLUMNS_TYPES)),
            ARRAY_FILTER_USE_KEY
        );

        $success = false;
        if (!empty($newColumnValues)) {
            $success = $this->userRepository->updateById($userId, $newColumnValues);

            $loggedInUserId = $request->getAttribute('userId');
            $this->auditAction($loggedInUserId, $userId);

            $user = $this->userRepository->findById($userId);

            $templateVars = [
                'user_full_name' => $user['full_name'],
                'attributes' => array_keys($newColumnValues)
            ];
            $this->emailService->queueTemplatedEmail('users/changed', $templateVars, 'Your user has been changed', [$user['email']]);
        }

        return ['success' => $success];
    }

    private function auditAction(int $loggedInUserId, int $userId): void
    {
        $this->activityPublisherService->publish($loggedInUserId, AuditLogAction::USER_MODIFIED, ['type' => 'user', 'id' => $userId]);
    }
}
