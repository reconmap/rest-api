<?php declare(strict_types=1);

namespace Reconmap\Database;

use Reconmap\Models\Document;
use Reconmap\Models\Task;
use Reconmap\Models\Note;
use Reconmap\Repositories\DocumentRepository;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Repositories\NoteRepository;

class TestDataGenerator
{
    public function __construct(
        private TaskRepository     $taskRepository,
        private NoteRepository $noteRepository,
        private DocumentRepository $documentRepository)
    {

    }

    public function generate()
    {
        echo 'Generating test note...';
		$notes = [
			[ 'description' => 'Credentials are stored in the secret server' ],
			[ 'description' => 'The client asked not to touch the servers during office hours.' ]
		];
		foreach($notes as $noteData) {
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

        echo 'Generating test tasks...';
        $tasks = [
            [
                'creator_uid' => 1,
                'project_id' => 1,
                'summary' => 'Run port scanner',
                'description' => 'Use nmap to detect all open ports',
                'command_id' => 1,
            ],
        ];
        foreach ($tasks as $taskData) {
            $task = new Task();
            $task->creator_uid = $taskData['creator_uid'];
            $task->project_id = $taskData['project_id'];
            $task->summary = $taskData['summary'];
            $task->description = $taskData['description'];
            $task->command_id = $taskData['command_id'];
            $this->taskRepository->insert($task);
        }

        $task = new Task();
        $task->creator_uid = 1;
        $task->project_id = 1;
        $task->summary = 'Run port scanner';
        $task->description = 'Use nmap to detect all open ports';
        $task->command_id = 2;

        $task->creator_uid = 1;
        $task->project_id = 1;
        $task->summary = 'Run SQL injection scanner';
        $task->description = 'Use sqlmap to test the application for SQL injection vulnerabilities';
        $task->command_id = 4;
        $this->taskRepository->insert($task);

        $task->creator_uid = 1;
        $task->project_id = 1;
        $task->summary = 'Check domain expiration date';
        $task->description = 'Use whois or other tools to check when the domain expiration is.';
        $task->command_id = 3;
        $this->taskRepository->insert($task);

        $task->creator_uid = 1;
        $task->project_id = 2;
        $task->summary = 'Run port scanner';
        $task->description = 'Use nmap to detect all open ports';
        $task->command_id = 2;
        $this->taskRepository->insert($task);

        $task->creator_uid = 1;
        $task->project_id = 2;
        $task->summary = 'Run SQL injection scanner';
        $task->description = 'Use sqlmap to test the application for SQL injection vulnerabilities';
        $task->command_id = 4;
        $this->taskRepository->insert($task);

        $task->creator_uid = 1;
        $task->project_id = 2;
        $task->summary = 'Check domain expiration date';
        $task->description = 'Use whois or other tools to check when the domain expiration is.';
        $task->command_id = 3;
        $this->taskRepository->insert($task);
        echo 'Done!', PHP_EOL;
    }
}
