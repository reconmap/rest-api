<?php declare(strict_types=1);

namespace Reconmap\Services\Reporting;

use Psr\Log\LoggerInterface;
use Reconmap\Repositories\ProjectRepository;
use Reconmap\Repositories\SearchCriterias\ProjectSearchCriteria;
use Reconmap\Repositories\TaskRepository;
use Reconmap\Repositories\UserRepository;
use Reconmap\Services\EmailService;

class WeeklyReportGenerator
{
    public function __construct(private ProjectRepository $projectRepository,
                                private UserRepository    $userRepository,
                                private TaskRepository    $taskRepository,
                                private EmailService      $emailService,
                                private LoggerInterface   $logger)
    {
    }

    public function generate()
    {
        $searchCriteria = new ProjectSearchCriteria();
        $searchCriteria->addArchivedCriterion(false);
        $searchCriteria->addIsNotTemplateCriterion();
        $projects = $this->projectRepository->search($searchCriteria);

        foreach ($projects as $project) {
            $this->logger->info("sending weekly report", [$project['id']]);
            $users = $this->userRepository->findByProjectId($project['id']);

            foreach ($users as $user) {
                $recipients = [$user['email'] => $user['full_name']];
                $this->logger->info('to', $recipients);
                $tasks = $this->taskRepository->findByProjectId($project['id']);

                $vars = [
                    'user' => $user,
                    'project' => $project,
                    'number_tasks' => array_reduce($tasks, fn(int $carry, array $task): int => $carry + ($task['status'] === 'done' ? 1 : 0), 0),
                    'number_pending_tasks' => array_reduce($tasks, fn(int $carry, array $task): int => $carry + ($task['status'] !== 'done' ? 1 : 0), 0)
                ];
                $this->emailService->queueTemplatedEmail('projects/weekly-report', $vars, 'Weekly project summary: ' . $project['name'], $recipients);
            }
        }
    }
}
