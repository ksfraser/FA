<?php
/**
 * FrontAccounting Timesheet Management Module
 *
 * Comprehensive time tracking system for employees and projects.
 *
 * @package FA\Modules\TimesheetManagement
 * @version 1.0.0
 * @author FrontAccounting Team
 * @license GPL-3.0
 */

namespace FA\Modules\TimesheetManagement;

use FA\Events\EventDispatcherInterface;
use FA\Database\DBALInterface;
use FA\Modules\EmployeeManagement\EmployeeService;
use FA\Modules\ProjectManagement\ProjectService;
use Psr\Log\LoggerInterface;

/**
 * Timesheet Service
 *
 * Handles time tracking, timesheet management, and billing integration
 */
class TimesheetService
{
    private DBALInterface $db;
    private EventDispatcherInterface $events;
    private LoggerInterface $logger;
    private EmployeeService $employeeService;
    private ProjectService $projectService;

    public function __construct(
        DBALInterface $db,
        EventDispatcherInterface $events,
        LoggerInterface $logger,
        EmployeeService $employeeService,
        ProjectService $projectService
    ) {
        $this->db = $db;
        $this->events = $events;
        $this->logger = $logger;
        $this->employeeService = $employeeService;
        $this->projectService = $projectService;
    }

    /**
     * Create a new timesheet entry
     *
     * @param array $timesheetData Timesheet entry data
     * @return TimesheetEntry The created timesheet entry
     * @throws TimesheetException
     */
    public function createTimesheetEntry(array $timesheetData): TimesheetEntry
    {
        $this->logger->info('Creating timesheet entry', [
            'employeeId' => $timesheetData['employeeId'] ?? '',
            'date' => $timesheetData['date'] ?? ''
        ]);

        $this->validateTimesheetData($timesheetData);

        $entryId = $this->getNextEntryId();

        $entry = new TimesheetEntry(
            $entryId,
            $timesheetData['employeeId'],
            new \DateTime($timesheetData['date']),
            $timesheetData['hours'],
            $timesheetData['activity']
        );

        // Set optional fields
        if (isset($timesheetData['projectId'])) {
            $entry->setProjectId($timesheetData['projectId']);
        }
        if (isset($timesheetData['taskId'])) {
            $entry->setTaskId($timesheetData['taskId']);
        }
        if (isset($timesheetData['description'])) {
            $entry->setDescription($timesheetData['description']);
        }
        if (isset($timesheetData['billable'])) {
            $entry->setBillable((bool)$timesheetData['billable']);
        }
        if (isset($timesheetData['billingRate'])) {
            $entry->setBillingRate((float)$timesheetData['billingRate']);
        }
        if (isset($timesheetData['status'])) {
            $entry->setStatus($timesheetData['status']);
        }

        // Calculate billing amount if billable
        if ($entry->isBillable() && $entry->getBillingRate() > 0) {
            $entry->setBillingAmount($entry->getHours() * $entry->getBillingRate());
        }

        // Save to database
        $this->saveTimesheetEntry($entry);

        $this->events->dispatch(new TimesheetEntryCreatedEvent($entry));

        $this->logger->info('Timesheet entry created successfully', ['entryId' => $entryId]);

        return $entry;
    }

    /**
     * Submit timesheet for approval
     *
     * @param string $employeeId Employee ID
     * @param string $weekStartDate Week start date (YYYY-MM-DD)
     * @throws TimesheetException
     */
    public function submitTimesheet(string $employeeId, string $weekStartDate): void
    {
        $this->logger->info('Submitting timesheet for approval', [
            'employeeId' => $employeeId,
            'weekStart' => $weekStartDate
        ]);

        // Get all entries for the week
        $entries = $this->getTimesheetEntries($employeeId, $weekStartDate, 'Draft');

        if (empty($entries)) {
            throw new TimesheetException("No draft entries found for the specified week");
        }

        // Update status to Submitted
        $this->db->beginTransaction();

        try {
            foreach ($entries as $entry) {
                $this->updateTimesheetStatus($entry->getEntryId(), 'Submitted');
            }

            $this->db->commit();

            $this->events->dispatch(new TimesheetSubmittedEvent($employeeId, $weekStartDate, $entries));

        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Approve timesheet
     *
     * @param string $employeeId Employee ID
     * @param string $weekStartDate Week start date
     * @param string $approverId Approver employee ID
     * @throws TimesheetException
     */
    public function approveTimesheet(string $employeeId, string $weekStartDate, string $approverId): void
    {
        $this->logger->info('Approving timesheet', [
            'employeeId' => $employeeId,
            'weekStart' => $weekStartDate,
            'approver' => $approverId
        ]);

        $entries = $this->getTimesheetEntries($employeeId, $weekStartDate, 'Submitted');

        if (empty($entries)) {
            throw new TimesheetException("No submitted entries found for the specified week");
        }

        $this->db->beginTransaction();

        try {
            foreach ($entries as $entry) {
                $this->updateTimesheetStatus($entry->getEntryId(), 'Approved', $approverId);
            }

            $this->db->commit();

            $this->events->dispatch(new TimesheetApprovedEvent($employeeId, $weekStartDate, $approverId, $entries));

        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Get timesheet entries for an employee and week
     *
     * @param string $employeeId Employee ID
     * @param string $weekStartDate Week start date
     * @param string|null $status Filter by status
     * @return TimesheetEntry[]
     */
    public function getTimesheetEntries(string $employeeId, string $weekStartDate, ?string $status = null): array
    {
        $weekEndDate = date('Y-m-d', strtotime($weekStartDate . ' +6 days'));

        $sql = "SELECT * FROM timesheet_entries
                WHERE employee_id = ?
                AND date BETWEEN ? AND ?";

        $params = [$employeeId, $weekStartDate, $weekEndDate];

        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY date, entry_id";

        $results = $this->db->fetchAll($sql, $params);

        $entries = [];
        foreach ($results as $result) {
            $entry = new TimesheetEntry(
                $result['entry_id'],
                $result['employee_id'],
                new \DateTime($result['date']),
                (float)$result['hours'],
                $result['activity']
            );

            $entry->setProjectId($result['project_id'] ?? '');
            $entry->setTaskId($result['task_id'] ?? '');
            $entry->setDescription($result['description'] ?? '');
            $entry->setBillable((bool)$result['billable']);
            $entry->setBillingRate((float)$result['billing_rate']);
            $entry->setBillingAmount((float)$result['billing_amount']);
            $entry->setStatus($result['status']);
            $entry->setApprovedBy($result['approved_by'] ?? '');
            if ($result['approved_date']) {
                $entry->setApprovedDate(new \DateTime($result['approved_date']));
            }

            $entries[] = $entry;
        }

        return $entries;
    }

    /**
     * Get timesheet summary for billing
     *
     * @param string $projectId Project ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Billing summary
     */
    public function getBillingSummary(string $projectId, string $startDate, string $endDate): array
    {
        $sql = "SELECT
                    te.employee_id,
                    e.first_name,
                    e.last_name,
                    SUM(te.hours) as total_hours,
                    SUM(te.billing_amount) as total_billing,
                    AVG(te.billing_rate) as avg_rate
                FROM timesheet_entries te
                INNER JOIN employees e ON te.employee_id = e.employee_id
                WHERE te.project_id = ?
                AND te.date BETWEEN ? AND ?
                AND te.status = 'Approved'
                AND te.billable = 1
                GROUP BY te.employee_id, e.first_name, e.last_name
                ORDER BY e.last_name, e.first_name";

        $results = $this->db->fetchAll($sql, [$projectId, $startDate, $endDate]);

        return $results;
    }

    /**
     * Get employee utilization report
     *
     * @param string $employeeId Employee ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Utilization data
     */
    public function getEmployeeUtilization(string $employeeId, string $startDate, string $endDate): array
    {
        $sql = "SELECT
                    DATE_FORMAT(date, '%Y-%m') as month,
                    SUM(hours) as total_hours,
                    SUM(CASE WHEN project_id IS NOT NULL THEN hours ELSE 0 END) as project_hours,
                    SUM(CASE WHEN project_id IS NULL THEN hours ELSE 0 END) as non_project_hours
                FROM timesheet_entries
                WHERE employee_id = ?
                AND date BETWEEN ? AND ?
                AND status = 'Approved'
                GROUP BY DATE_FORMAT(date, '%Y-%m')
                ORDER BY month";

        $results = $this->db->fetchAll($sql, [$employeeId, $startDate, $endDate]);

        return $results;
    }

    /**
     * Validate timesheet data
     *
     * @param array $data
     * @throws TimesheetException
     */
    private function validateTimesheetData(array $data): void
    {
        if (empty($data['employeeId'])) {
            throw new TimesheetException("Employee ID is required");
        }

        if (empty($data['date'])) {
            throw new TimesheetException("Date is required");
        }

        if (!isset($data['hours']) || $data['hours'] <= 0 || $data['hours'] > 24) {
            throw new TimesheetException("Hours must be between 0.01 and 24");
        }

        if (empty($data['activity'])) {
            throw new TimesheetException("Activity description is required");
        }

        // Validate employee exists
        $this->employeeService->getEmployee($data['employeeId']);

        // Validate project/task if provided
        if (!empty($data['projectId'])) {
            $this->projectService->getProject($data['projectId']);
        }

        if (!empty($data['taskId'])) {
            $this->projectService->getTask($data['taskId']);
        }
    }

    /**
     * Get next entry ID
     *
     * @return string
     */
    private function getNextEntryId(): string
    {
        $sql = "SELECT MAX(CAST(entry_id AS UNSIGNED)) + 1 as next_id FROM timesheet_entries";
        $result = $this->db->fetchAssoc($sql);

        return (string)($result['next_id'] ?? 1);
    }

    /**
     * Save timesheet entry to database
     *
     * @param TimesheetEntry $entry
     */
    private function saveTimesheetEntry(TimesheetEntry $entry): void
    {
        $sql = "INSERT INTO timesheet_entries (
                    entry_id, employee_id, date, hours, activity, project_id,
                    task_id, description, billable, billing_rate, billing_amount, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $this->db->executeUpdate($sql, [
            $entry->getEntryId(),
            $entry->getEmployeeId(),
            $entry->getDate()->format('Y-m-d'),
            $entry->getHours(),
            $entry->getActivity(),
            $entry->getProjectId(),
            $entry->getTaskId(),
            $entry->getDescription(),
            $entry->isBillable() ? 1 : 0,
            $entry->getBillingRate(),
            $entry->getBillingAmount(),
            $entry->getStatus()
        ]);
    }

    /**
     * Update timesheet entry status
     *
     * @param string $entryId Entry ID
     * @param string $status New status
     * @param string|null $approverId Approver ID
     */
    private function updateTimesheetStatus(string $entryId, string $status, ?string $approverId = null): void
    {
        $sql = "UPDATE timesheet_entries SET status = ?";
        $params = [$status];

        if ($approverId) {
            $sql .= ", approved_by = ?, approved_date = NOW()";
            $params[] = $approverId;
        }

        $sql .= " WHERE entry_id = ?";
        $params[] = $entryId;

        $this->db->executeUpdate($sql, $params);
    }
}