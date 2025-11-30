<?php
/**
 * Timesheet Management Entities
 */

namespace FA\Modules\TimesheetManagement;

/**
 * Timesheet Entry Entity
 */
class TimesheetEntry
{
    private string $entryId;
    private string $employeeId;
    private \DateTime $date;
    private float $hours;
    private string $activity;
    private string $projectId;
    private string $taskId;
    private string $description;
    private bool $billable;
    private float $billingRate;
    private float $billingAmount;
    private string $status;
    private string $approvedBy;
    private ?\DateTime $approvedDate;

    public function __construct(
        string $entryId,
        string $employeeId,
        \DateTime $date,
        float $hours,
        string $activity
    ) {
        $this->entryId = $entryId;
        $this->employeeId = $employeeId;
        $this->date = $date;
        $this->hours = $hours;
        $this->activity = $activity;
        $this->billable = false;
        $this->billingRate = 0.0;
        $this->billingAmount = 0.0;
        $this->status = 'Draft';
    }

    public function getEntryId(): string { return $this->entryId; }
    public function getEmployeeId(): string { return $this->employeeId; }
    public function getDate(): \DateTime { return $this->date; }
    public function getHours(): float { return $this->hours; }
    public function getActivity(): string { return $this->activity; }
    public function getProjectId(): string { return $this->projectId ?? ''; }
    public function getTaskId(): string { return $this->taskId ?? ''; }
    public function getDescription(): string { return $this->description ?? ''; }
    public function isBillable(): bool { return $this->billable; }
    public function getBillingRate(): float { return $this->billingRate; }
    public function getBillingAmount(): float { return $this->billingAmount; }
    public function getStatus(): string { return $this->status; }
    public function getApprovedBy(): string { return $this->approvedBy ?? ''; }
    public function getApprovedDate(): ?\DateTime { return $this->approvedDate; }

    public function setProjectId(string $projectId): self
    {
        $this->projectId = $projectId;
        return $this;
    }

    public function setTaskId(string $taskId): self
    {
        $this->taskId = $taskId;
        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function setBillable(bool $billable): self
    {
        $this->billable = $billable;
        return $this;
    }

    public function setBillingRate(float $rate): self
    {
        $this->billingRate = $rate;
        return $this;
    }

    public function setBillingAmount(float $amount): self
    {
        $this->billingAmount = $amount;
        return $this;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function setApprovedBy(string $approvedBy): self
    {
        $this->approvedBy = $approvedBy;
        return $this;
    }

    public function setApprovedDate(\DateTime $date): self
    {
        $this->approvedDate = $date;
        return $this;
    }
}