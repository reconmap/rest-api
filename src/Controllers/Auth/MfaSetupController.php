<?php declare(strict_types=1);

namespace Reconmap\Controllers\Auth;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use PragmaRX\Google2FA\Google2FA;
use Psr\Http\Message\ServerRequestInterface;
use Reconmap\Controllers\Controller;
use Reconmap\Models\AuditActions\AuditLogAction;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\AuditLogService;

class MfaSetupController extends Controller
{
    public function __construct(
        private Google2FA       $google2FA,
        private UserRepository  $userRepository,
        private AuditLogService $auditLogService)
    {
    }

    public function __invoke(ServerRequestInterface $request): array
    {
        $userId = $request->getAttribute('userId');

        $user = $this->userRepository->findById($userId);

        $mfaSecret = $this->google2FA->generateSecretKey();
        $this->userRepository->updateById($userId, ['mfa_secret' => $mfaSecret]);

        $qrCodeUrl = $this->google2FA->getQRCodeUrl(
            "Reconmap",
            $user['email'],
            $mfaSecret
        );

        $this->logger->debug("URL : $qrCodeUrl");

        $result = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($qrCodeUrl)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(300)
            ->margin(10)
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->build();
        $dataUri = $result->getDataUri();

        $this->auditLogService->insert($userId, AuditLogAction::USER_LOGGED_OUT);

        return ['qrDataUri' => $dataUri, 'secret' => $mfaSecret];
    }
}
