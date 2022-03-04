<?php declare(strict_types=1);

namespace Reconmap\Database;

use Reconmap\Models\Client;
use Reconmap\Models\Contact;
use Reconmap\Models\Document;
use Reconmap\Repositories\ClientRepository;
use Reconmap\Repositories\ContactRepository;
use Reconmap\Repositories\DocumentRepository;

class TestDataGenerator
{
    public function __construct(
        private readonly UserTestDataGenerator $userTestDataGenerator,
        private readonly ContactRepository $contactRepository,
        private readonly ClientRepository $clientRepository,
        private readonly ProjectTestDataGenerator $projectTestDataGenerator,
        private readonly NoteTestDataGenerator $noteTestDataGenerator,
        private readonly DocumentRepository $documentRepository,
        private readonly TaskTestDataGenerator $taskTestDataGenerator,
        private readonly VulnerabilityTestDataGenerator $vulnerabilityTestDataGenerator)
    {
    }

    public function generate()
    {
        $contact = new Contact();
        $contact->kind = 'billing';
        $contact->name = 'John Doe';
        $contact->email = 'John.Doe@in.se.cure';
        $contact->phone = '+99 123 245 389';
        $contact->id = $this->contactRepository->insert($contact);

        $client = new Client();
        $client->creator_uid = 1;
        $client->name = 'Insecure Co.';
        $client->url = 'http://in.se.cure';
        $client->contact_id = $contact->id;
        $this->clientRepository->insert($client);

        $contact = new Contact();
        $contact->kind = 'billing';
        $contact->name = 'Jane Doe';
        $contact->email = 'Jane.Doe@in.se.cure';
        $contact->phone = '+1 123 245 389';
        $contact->id = $this->contactRepository->insert($contact);

        $client = new Client();
        $client->creator_uid = 1;
        $client->name = 'The OWASP Foundation';
        $client->url = 'https://owasp.org';
        $client->contact_id = $contact->id;
        $this->clientRepository->insert($client);

        $document = new Document();
        $document->user_id = 1;
        $document->visibility = 'public';
        $document->parent_type = 'library';
        $document->content = 'Some';
        $document->title = 'Thing';
        $this->documentRepository->insert($document);

        $this->projectTestDataGenerator->run();;
        $this->userTestDataGenerator->run();
        $this->noteTestDataGenerator->run();
        $this->taskTestDataGenerator->run();
        $this->vulnerabilityTestDataGenerator->run();
    }
}
