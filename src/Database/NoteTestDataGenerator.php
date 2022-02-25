<?php declare(strict_types=1);

namespace Reconmap\Database;

use Reconmap\Models\Note;
use Reconmap\Repositories\NoteRepository;

class NoteTestDataGenerator
{
    public function __construct(private readonly NoteRepository $noteRepository)
    {
    }

    public function run(): void
    {
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
    }
}
