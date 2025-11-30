<?php
/**
 * FrontAccounting HR and Project Management Integration Service
 *
 * Unified service providing comprehensive HR and project management capabilities
 * similar to OrangeHRM and dotProject/OpenProject.
 *
 * @package FA\Modules
 * @version 1.0.0
 * @author FrontAccounting Team
 * @license GPL-3.0
 */

namespace FA\Modules;

use FA\Modules\EmployeeManagement\EmployeeService;
use FA\Modules\TimesheetManagement\TimesheetService;
use FA\Modules\ProjectManagement\ProjectService;
use FA\Database\DBALInterface;
use FA\Events\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

/**
 * HR and Project Management Integration Service
 *
 * Provides unified interface for employee management, timesheets, and projects
 */
class HRProjectManagementService
{
    private EmployeeService $employeeService;
    private TimesheetService $timesheetService;
    private ProjectService $projectService;
    private DBALInterface $db;
    private EventDispatcherInterface $events;
    private LoggerInterface $logger;

    public function __construct(
        EmployeeService $employeeService,
        TimesheetService $timesheetService,
        ProjectService $projectService,
        DBALInterface $db,
        EventDispatcherInterface $events,
        LoggerInterface $logger
    ) {
        $this->employeeService = $employeeService;
        $this->timesheetService = $timesheetService;
        $this->projectService = $projectService;
        $this->db = $db;
        $this->events = $events;
        $this->logger = $logger;
    }

    /**
     * Create complete employee profile with skills and training
     *
     * @param array $employeeData Employee basic information
     * @param array $skills Employee skills
     * @param array $training Employee training records
     * @return string Employee ID
     */
    public function createEmployeeProfile(array $employeeData, array $skills = [], array $training = []): string
    {
        $this->logger->info('Creating complete employee profile', ['name' => $employeeData['firstName'] . ' ' . $employeeData['lastName']]);

        // Create employee
        $employee = $this->employeeService->createEmployee($employeeData);
        $employeeId = $employee->getEmployeeId();

        // Add skills
        foreach ($skills as $skill) {
            $this->employeeService->addEmployeeSkill($employeeId, $skill);
        }

        // Add training records
        foreach ($training as $trainingRecord) {
            $this->employeeService->recordEmployeeTraining($employeeId, $trainingRecord);
        }

        $this->logger->info('Employee profile created successfully', ['employeeId' => $employeeId]);

        return $employeeId;
    }

    /**
     * Create project with team assignment and initial tasks
     *
     * @param array $projectData Project information
     * @param array $teamMembers Team member assignments
     * @param array $initialTasks Initial project tasks
     * @return string Project ID
     */
    public function createProjectWithTeam(array $projectData, array $teamMembers = [], array $initialTasks = []): string
    {
        $this->logger->info('Creating project with team and tasks', ['projectName' => $projectData['name']]);

        // Create project
        $project = $this->projectService->createProject($projectData);
        $projectId = $project->getProjectId();

        // Assign team members
        foreach ($teamMembers as $member) {
            $this->projectService->assignEmployeeToProject($projectId, $member['employeeId'], $member);
        }

        // Create initial tasks
        foreach ($initialTasks as $task) {
            $task['projectId'] = $projectId;
            $this->projectService->createTask($task);
        }

        $this->logger->info('Project with team created successfully', ['projectId' => $projectId]);

        return $projectId;
    }

    /**
     * Record timesheet entry with project and task integration
     *
     * @param array $timesheetData Timesheet entry data
     * @return string Timesheet entry ID
     */
    public function recordWorkTime(array $timesheetData): string
    {
        $this->logger->info('Recording work time', [
            'employeeId' => $timesheetData['employeeId'],
            'projectId' => $timesheetData['projectId'] ?? '',
            'taskId' => $timesheetData['taskId'] ?? ''
        ]);

        // Create timesheet entry
        $entry = $this->timesheetService->createTimesheetEntry($timesheetData);
        $entryId = $entry->getEntryId();

        // Update task progress if task is specified
        if (!empty($timesheetData['taskId'])) {
            $this->updateTaskProgressFromTimesheet($timesheetData['taskId']);
        }

        $this->logger->info('Work time recorded successfully', ['entryId' => $entryId]);

        return $entryId;
    }

    /**
     * Get employee utilization report
     *
     * @param string $employeeId Employee ID
     * @param \DateTime $startDate Start date
     * @param \DateTime $endDate End date
     * @return array Utilization data
     */
    public function getEmployeeUtilization(string $employeeId, \DateTime $startDate, \DateTime $endDate): array
    {
        $this->logger->info('Getting employee utilization', ['employeeId' => $employeeId]);

        // Get timesheet entries for period
        $entries = $this->timesheetService->getEmployeeTimesheetEntries($employeeId, $startDate, $endDate);

        $totalHours = 0;
        $billableHours = 0;
        $projectHours = [];

        foreach ($entries as $entry) {
            $hours = $entry->getHours();
            $totalHours += $hours;

            if ($entry->isBillable()) {
                $billableHours += $hours;
            }

            $projectId = $entry->getProjectId();
            if ($projectId) {
                if (!isset($projectHours[$projectId])) {
                    $projectHours[$projectId] = 0;
                }
                $projectHours[$projectId] += $hours;
            }
        }

        // Get project names
        $projects = [];
        foreach ($projectHours as $projectId => $hours) {
            try {
                $project = $this->projectService->getProject($projectId);
                $projects[] = [
                    'projectId' => $projectId,
                    'projectName' => $project->getName(),
                    'hours' => $hours
                ];
            } catch (\Exception $e) {
                $projects[] = [
                    'projectId' => $projectId,
                    'projectName' => 'Unknown Project',
                    'hours' => $hours
                ];
            }
        }

        return [
            'employeeId' => $employeeId,
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ],
            'totalHours' => $totalHours,
            'billableHours' => $billableHours,
            'utilizationRate' => $totalHours > 0 ? ($billableHours / $totalHours) * 100 : 0,
            'projects' => $projects
        ];
    }

    /**
     * Get project progress report
     *
     * @param string $projectId Project ID
     * @return array Project progress data
     */
    public function getProjectProgress(string $projectId): array
    {
        $this->logger->info('Getting project progress', ['projectId' => $projectId]);

        $project = $this->projectService->getProject($projectId);
        $tasks = $this->projectService->getProjectTasks($projectId);
        $team = $this->projectService->getProjectTeam($projectId);

        // Calculate overall progress
        $totalTasks = count($tasks);
        $completedTasks = count(array_filter($tasks, fn($task) => $task->isCompleted()));
        $overallProgress = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;

        // Calculate budget utilization
        $budgetUtilization = $this->calculateProjectBudgetUtilization($projectId);

        // Get timesheet summary
        $timesheetSummary = $this->timesheetService->getProjectTimesheetSummary($projectId);

        return [
            'project' => [
                'id' => $project->getProjectId(),
                'name' => $project->getName(),
                'status' => $project->getStatus(),
                'progress' => $overallProgress,
                'budget' => $project->getBudget(),
                'budgetUtilization' => $budgetUtilization
            ],
            'tasks' => [
                'total' => $totalTasks,
                'completed' => $completedTasks,
                'inProgress' => count(array_filter($tasks, fn($task) => $task->getStatus() === 'In Progress')),
                'overdue' => count(array_filter($tasks, fn($task) => $task->isOverdue()))
            ],
            'team' => [
                'size' => count($team),
                'members' => array_map(function($member) {
                    return [
                        'employeeId' => $member['employee_id'],
                        'name' => $member['first_name'] . ' ' . $member['last_name'],
                        'role' => $member['role'],
                        'allocation' => $member['allocation_percentage']
                    ];
                }, $team)
            ],
            'timesheet' => $timesheetSummary
        ];
    }

    /**
     * Get comprehensive HR dashboard data
     *
     * @return array Dashboard data
     */
    public function getHRDashboard(): array
    {
        $this->logger->info('Getting HR dashboard data');

        // Get employee statistics
        $totalEmployees = $this->getTotalEmployeeCount();
        $activeProjects = $this->getActiveProjectCount();
        $pendingTimesheets = $this->getPendingTimesheetCount();

        // Get recent activities
        $recentHires = $this->getRecentHires(5);
        $upcomingReviews = $this->getUpcomingPerformanceReviews(5);
        $overdueTasks = $this->getOverdueTasks(5);

        return [
            'summary' => [
                'totalEmployees' => $totalEmployees,
                'activeProjects' => $activeProjects,
                'pendingTimesheets' => $pendingTimesheets
            ],
            'recentHires' => $recentHires,
            'upcomingReviews' => $upcomingReviews,
            'overdueTasks' => $overdueTasks
        ];
    }

    /**
     * Update task progress based on timesheet entries
     *
     * @param string $taskId Task ID
     */
    private function updateTaskProgressFromTimesheet(string $taskId): void
    {
        try {
            $task = $this->projectService->getTask($taskId);

            // Get total hours logged for this task
            $sql = "SELECT SUM(hours) as total_hours FROM timesheet_entries
                    WHERE task_id = ? AND status = 'Approved'";
            $result = $this->db->fetchAssoc($sql, [$taskId]);
            $loggedHours = (float)($result['total_hours'] ?? 0);

            // Update actual hours
            $task->setActualHours($loggedHours);

            // Calculate progress based on estimated vs actual hours
            $estimatedHours = $task->getEstimatedHours();
            if ($estimatedHours > 0) {
                $progress = min(100, ($loggedHours / $estimatedHours) * 100);
                $task->setProgress($progress);

                // Auto-update status based on progress
                if ($progress >= 100) {
                    $task->setStatus('Completed');
                } elseif ($progress > 0) {
                    $task->setStatus('In Progress');
                }
            }

            // Save updated task (this would need to be added to ProjectService)
            // For now, we'll update directly
            $this->db->executeUpdate(
                "UPDATE project_tasks SET actual_hours = ?, progress = ?, status = ? WHERE task_id = ?",
                [$loggedHours, $task->getProgress(), $task->getStatus(), $taskId]
            );

        } catch (\Exception $e) {
            $this->logger->warning('Failed to update task progress from timesheet', [
                'taskId' => $taskId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Calculate project budget utilization
     *
     * @param string $projectId Project ID
     * @return float Budget utilization percentage
     */
    private function calculateProjectBudgetUtilization(string $projectId): float
    {
        $project = $this->projectService->getProject($projectId);
        $budget = $project->getBudget();

        if ($budget <= 0) {
            return 0;
        }

        // Get total billable hours for project
        $sql = "SELECT SUM(te.hours * te.billing_rate) as total_billed
                FROM timesheet_entries te
                WHERE te.project_id = ? AND te.is_billable = 1 AND te.status = 'Approved'";

        $result = $this->db->fetchAssoc($sql, [$projectId]);
        $totalBilled = (float)($result['total_billed'] ?? 0);

        return ($totalBilled / $budget) * 100;
    }

    /**
     * Get total employee count
     *
     * @return int
     */
    private function getTotalEmployeeCount(): int
    {
        $sql = "SELECT COUNT(*) as count FROM employees WHERE status = 'Active'";
        $result = $this->db->fetchAssoc($sql);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Get active project count
     *
     * @return int
     */
    private function getActiveProjectCount(): int
    {
        $sql = "SELECT COUNT(*) as count FROM projects WHERE status IN ('Planning', 'In Progress', 'On Hold')";
        $result = $this->db->fetchAssoc($sql);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Get pending timesheet count
     *
     * @return int
     */
    private function getPendingTimesheetCount(): int
    {
        $sql = "SELECT COUNT(*) as count FROM timesheet_entries WHERE status = 'Pending'";
        $result = $this->db->fetchAssoc($sql);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Get recent hires
     *
     * @param int $limit Number of records to return
     * @return array Recent hires
     */
    private function getRecentHires(int $limit): array
    {
        $sql = "SELECT employee_id, first_name, last_name, hire_date, job_title
                FROM employees
                WHERE hire_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                ORDER BY hire_date DESC
                LIMIT ?";

        $results = $this->db->fetchAll($sql, [$limit]);

        return array_map(function($row) {
            return [
                'employeeId' => $row['employee_id'],
                'name' => $row['first_name'] . ' ' . $row['last_name'],
                'hireDate' => $row['hire_date'],
                'jobTitle' => $row['job_title']
            ];
        }, $results);
    }

    /**
     * Get upcoming performance reviews
     *
     * @param int $limit Number of records to return
     * @return array Upcoming reviews
     */
    private function getUpcomingPerformanceReviews(int $limit): array
    {
        $sql = "SELECT e.employee_id, e.first_name, e.last_name, e.next_review_date
                FROM employees e
                WHERE e.next_review_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                ORDER BY e.next_review_date ASC
                LIMIT ?";

        $results = $this->db->fetchAll($sql, [$limit]);

        return array_map(function($row) {
            return [
                'employeeId' => $row['employee_id'],
                'name' => $row['first_name'] . ' ' . $row['last_name'],
                'reviewDate' => $row['next_review_date']
            ];
        }, $results);
    }

    /**
     * Get overdue tasks
     *
     * @param int $limit Number of records to return
     * @return array Overdue tasks
     */
    private function getOverdueTasks(int $limit): array
    {
        $sql = "SELECT t.task_id, t.name, t.end_date, p.name as project_name
                FROM project_tasks t
                INNER JOIN projects p ON t.project_id = p.project_id
                WHERE t.end_date < CURDATE()
                AND t.status NOT IN ('Completed', 'Cancelled')
                ORDER BY t.end_date ASC
                LIMIT ?";

        $results = $this->db->fetchAll($sql, [$limit]);

        return array_map(function($row) {
            return [
                'taskId' => $row['task_id'],
                'taskName' => $row['name'],
                'dueDate' => $row['end_date'],
                'projectName' => $row['project_name']
            ];
        }, $results);
    }
}