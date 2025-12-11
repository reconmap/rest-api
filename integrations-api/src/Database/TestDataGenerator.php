<?php

declare(strict_types=1);

namespace Reconmap\Database;

use Reconmap\Models\Client;
use Reconmap\Models\Contact;
use Reconmap\Models\Document;
use Reconmap\Models\Notification;
use Reconmap\Models\Report;
use Reconmap\Models\Target;
use Reconmap\Repositories\ClientRepository;
use Reconmap\Repositories\ContactRepository;
use Reconmap\Repositories\DocumentRepository;
use Reconmap\Repositories\NotificationsRepository;
use Reconmap\Repositories\ProjectUserRepository;
use Reconmap\Repositories\ReportRepository;
use Reconmap\Repositories\TargetRepository;

readonly class TestDataGenerator
{
    public function __construct(
        private UserTestDataGenerator          $userTestDataGenerator,
        private ContactRepository              $contactRepository,
        private ClientRepository               $clientRepository,
        private ProjectTestDataGenerator       $projectTestDataGenerator,
        private NoteTestDataGenerator          $noteTestDataGenerator,
        private DocumentRepository             $documentRepository,
        private TaskTestDataGenerator          $taskTestDataGenerator,
        private VulnerabilityTestDataGenerator $vulnerabilityTestDataGenerator,
        private NotificationsRepository        $notificationsRepository,
        private ReportRepository               $reportRepository,
        private ProjectUserRepository          $projectUserRepository,
        private TargetRepository               $targetRepository,
        private CommandTestDataGenerator       $commandTestDataGenerator
    ) {}

    public function generate(): void
    {
        $client = new Client();
        $client->kind = 'client';
        $client->created_by_uid = 1;
        $client->name = 'Insecure Co.';
        $client->url = 'http://in.se.cure';
        $client->address = 'Fake address 124';
        $clientId = $this->clientRepository->insert($client);

        $contact = new Contact();
        $contact->organisation_id = $clientId;
        $contact->kind = 'billing';
        $contact->name = 'John Doe';
        $contact->email = 'John.Doe@in.se.cure';
        $contact->phone = '+99 123 245 389';
        $contact->id = $this->contactRepository->insert($contact);

        $contact = new Contact();
        $contact->organisation_id = $clientId;
        $contact->kind = 'billing';
        $contact->name = 'Jane Doe';
        $contact->email = 'Jane.Doe@in.se.cure';
        $contact->phone = '+1 123 245 389';
        $contact->id = $this->contactRepository->insert($contact);

        $client = new Client();
        $client->kind = 'client';
        $client->created_by_uid = 1;
        $client->name = 'The OWASP Foundation';
        $client->url = 'https://owasp.org';
        $client->address = 'Fake address 124';
        $clientId = $this->clientRepository->insert($client);

        $document = new Document();
        $document->created_by_uid = 1;
        $document->visibility = 'public';
        $document->parent_type = 'library';
        $document->content = 'Some';
        $document->title = 'Thing';
        $this->documentRepository->insert($document);

        $notification = new Notification();
        $notification->toUserId = 1;
        $notification->title = 'Command completed';
        $notification->content = '100 vulnerabilities have been found';
        $this->notificationsRepository->insert($notification);

        $this->projectTestDataGenerator->run();
        $target = new Target();
        $target->project_id = 1;
        $target->name = 'https://test.com';
        $target->kind = 'url';
        $target->tags = null;
        $this->targetRepository->insert($target);

        $target = new Target();
        $target->project_id = 2;
        $target->name = '127.0.0.1';
        $target->kind = 'hostname';
        $target->tags = '[
         "linux",
         "dev-environment"
       ]';
        $this->targetRepository->insert($target);

        $this->commandTestDataGenerator->run();
        $this->userTestDataGenerator->run();
        $this->noteTestDataGenerator->run();
        $this->taskTestDataGenerator->run();
        $this->vulnerabilityTestDataGenerator->run();

        $report = new Report();
        $report->project_id = 2;
        $report->created_by_uid = 1;
        $report->version_name = '1.0';
        $report->version_description = 'Initial version';
        $this->reportRepository->insert($report);

        $report = new Report();
        $report->project_id = 2;
        $report->created_by_uid = 1;
        $report->version_name = '1.1';
        $report->version_description = 'Initial version after corrections';
        $this->reportRepository->insert($report);

        $report = new Report();
        $report->project_id = 2;
        $report->created_by_uid = 1;
        $report->version_name = '1.2 reviewed';
        $report->version_description = 'Report reviewed and sent to the client';
        $this->reportRepository->insert($report);

        $this->projectUserRepository->create(2, 1);
        $this->projectUserRepository->create(2, 2);
    }
}
