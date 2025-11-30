<?php
/**
 * FrontAccounting Employee Management Events
 *
 * Event classes for employee management functionality.
 *
 * @package FA\Modules\EmployeeManagement
 * @version 1.0.0
 * @author FrontAccounting Team
 * @license GPL-3.0
 */

namespace FA\Modules\EmployeeManagement;

/**
 * Base Employee Event
 */
abstract class EmployeeEvent
{
    protected Employee $employee;

    public function __construct(Employee $employee)
    {
        $this->employee = $employee;
    }

    public function getEmployee(): Employee
    {
        return $this->employee;
    }
}

/**
 * Employee Created Event
 */
class EmployeeCreatedEvent extends EmployeeEvent
{
    // Event data available through parent
}

/**
 * Employee Updated Event
 */
class EmployeeUpdatedEvent extends EmployeeEvent
{
    // Event data available through parent
}

/**
 * Employee Skill Added Event
 */
class EmployeeSkillAddedEvent
{
    private string $employeeId;
    private array $skillData;

    public function __construct(string $employeeId, array $skillData)
    {
        $this->employeeId = $employeeId;
        $this->skillData = $skillData;
    }

    public function getEmployeeId(): string
    {
        return $this->employeeId;
    }

    public function getSkillData(): array
    {
        return $this->skillData;
    }
}

/**
 * Employee Training Recorded Event
 */
class EmployeeTrainingRecordedEvent
{
    private string $employeeId;
    private array $trainingData;

    public function __construct(string $employeeId, array $trainingData)
    {
        $this->employeeId = $employeeId;
        $this->trainingData = $trainingData;
    }

    public function getEmployeeId(): string
    {
        return $this->employeeId;
    }

    public function getTrainingData(): array
    {
        return $this->trainingData;
    }
}