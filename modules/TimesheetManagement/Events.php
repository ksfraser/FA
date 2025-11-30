<?php
/**
 * FrontAccounting Timesheet Management Events
 *
 * Event classes for timesheet management functionality.
 *
 * @package FA\Modules\TimesheetManagement
 * @version 1.0.0
 * @author FrontAccounting Team
 * @license GPL-3.0
 */

namespace FA\Modules\TimesheetManagement;

/**
 * Base Timesheet Event
 */
abstract class TimesheetEvent
{
    protected TimesheetEntry $entry;

    public function __construct(TimesheetEntry $entry)
    {
        $this->entry = $entry;
    }

    public function getEntry(): TimesheetEntry
    {
        return $this->entry;
    }
}

/**
 * Timesheet Entry Created Event
 */
class TimesheetEntryCreatedEvent extends TimesheetEvent
{
    // Event data available through parent
}

/**
 * Timesheet Submitted Event
 */
class TimesheetSubmittedEvent
{
    private string $employeeId;
    private \DateTime $periodStart;
    private \DateTime $periodEnd;
    private array $entries;

    public function __construct(string $employeeId, \DateTime $periodStart, \DateTime $periodEnd, array $entries)
    {
        $this->employeeId = $employeeId;
        $this->periodStart = $periodStart;
        $this->periodEnd = $periodEnd;
        $this->entries = $entries;
    }

    public function getEmployeeId(): string
    {
        return $this->employeeId;
    }

    public function getPeriodStart(): \DateTime
    {
        return $this->periodStart;
    }

    public function getPeriodEnd(): \DateTime
    {
        return $this->periodEnd;
    }

    public function getEntries(): array
    {
        return $this->entries;
    }
}

/**
 * Timesheet Approved Event
 */
class TimesheetApprovedEvent extends TimesheetEvent
{
    private string $approverId;

    public function __construct(TimesheetEntry $entry, string $approverId)
    {
        parent::__construct($entry);
        $this->approverId = $approverId;
    }

    public function getApproverId(): string
    {
        return $this->approverId;
    }
}