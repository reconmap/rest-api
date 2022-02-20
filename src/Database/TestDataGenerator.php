<?php declare(strict_types=1);

namespace Reconmap\Database;

use Reconmap\Models\Client;
use Reconmap\Models\Contact;
use Reconmap\Models\Document;
use Reconmap\Models\Note;
use Reconmap\Repositories\ClientRepository;
use Reconmap\Repositories\ContactRepository;
use Reconmap\Repositories\DocumentRepository;
use Reconmap\Repositories\NoteRepository;
use Reconmap\Repositories\UserRepository;

class TestDataGenerator
{
    public function __construct(
        private                    readonly UserRepository $userRepository,
        private                    readonly ContactRepository $contactRepository,
        private                    readonly ClientRepository $clientRepository,
        private NoteRepository     $noteRepository,
        private DocumentRepository $documentRepository,
        private                    readonly TaskTestDataGenerator $taskTestDataGenerator,
        private                    readonly VulnerabilityTestDataGenerator $vulnerabilityTestDataGenerator)
    {
    }

    public function generate()
    {
        $this->userRepository->updateById(1, [
            'full_name' => 'Jane Doe',
            'short_bio' => 'CEO and CTO of Amazing Pentest Company Limited'
        ]);

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

        echo 'Generating test note...';
        $notes = [
            ['description' => 'Credentials are stored in the secret server'],
            ['description' => 'The client asked not to touch the servers during office hours.']
        ];
        foreach ($notes as $noteData) {
            $note = new Note();
            $note->user_id = 1;
            $note->visibility = 'public';
            $note->parent_type = 'project';
            $note->parent_id = 1;
            $note->content = $noteData['description'];

            $this->noteRepository->insert($note);
        }
        echo 'Done!', PHP_EOL;

        echo 'Generating test document...';
        $document = new Document();
        $document->user_id = 1;
        $document->visibility = 'public';
        $document->parent_type = 'library';
        $document->content = 'Some';
        $document->title = 'Thing';

        $this->documentRepository->insert($document);
        echo 'Done!', PHP_EOL;

        $this->taskTestDataGenerator->run();
        $this->vulnerabilityTestDataGenerator->run();
    }
}
