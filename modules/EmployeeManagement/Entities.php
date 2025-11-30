<?php
/**
 * Employee Management Entities
 */

namespace FA\Modules\EmployeeManagement;

/**
 * Employee Entity
 */
class Employee
{
    private string $employeeId;
    private string $firstName;
    private string $lastName;
    private string $middleName;
    private string $email;
    private string $employeeNumber;
    private string $department;
    private string $jobTitle;
    private string $managerId;
    private \DateTime $hireDate;
    private string $status;

    public function __construct(
        string $employeeId,
        string $firstName,
        string $lastName,
        string $email,
        \DateTime $hireDate
    ) {
        $this->employeeId = $employeeId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->hireDate = $hireDate;
        $this->status = 'Active';
    }

    public function getEmployeeId(): string { return $this->employeeId; }
    public function getFirstName(): string { return $this->firstName; }
    public function getLastName(): string { return $this->lastName; }
    public function getMiddleName(): string { return $this->middleName ?? ''; }
    public function getFullName(): string {
        return trim($this->firstName . ' ' . ($this->middleName ?? '') . ' ' . $this->lastName);
    }
    public function getEmail(): string { return $this->email; }
    public function getEmployeeNumber(): string { return $this->employeeNumber ?? ''; }
    public function getDepartment(): string { return $this->department ?? ''; }
    public function getJobTitle(): string { return $this->jobTitle ?? ''; }
    public function getManagerId(): string { return $this->managerId ?? ''; }
    public function getHireDate(): \DateTime { return $this->hireDate; }
    public function getStatus(): string { return $this->status; }

    public function setMiddleName(string $middleName): self
    {
        $this->middleName = $middleName;
        return $this;
    }

    public function setEmployeeNumber(string $number): self
    {
        $this->employeeNumber = $number;
        return $this;
    }

    public function setDepartment(string $department): self
    {
        $this->department = $department;
        return $this;
    }

    public function setJobTitle(string $title): self
    {
        $this->jobTitle = $title;
        return $this;
    }

    public function setManagerId(string $managerId): self
    {
        $this->managerId = $managerId;
        return $this;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }
}

/**
 * Employee Skill Entity
 */
class EmployeeSkill
{
    private string $employeeId;
    private string $skillName;
    private int $proficiencyLevel;
    private int $yearsOfExperience;
    private ?\DateTime $certificationDate;
    private ?\DateTime $expiryDate;
    private string $notes;

    public function __construct(
        string $employeeId,
        string $skillName,
        int $proficiencyLevel,
        int $yearsOfExperience
    ) {
        $this->employeeId = $employeeId;
        $this->skillName = $skillName;
        $this->proficiencyLevel = $proficiencyLevel;
        $this->yearsOfExperience = $yearsOfExperience;
    }

    public function getEmployeeId(): string { return $this->employeeId; }
    public function getSkillName(): string { return $this->skillName; }
    public function getProficiencyLevel(): int { return $this->proficiencyLevel; }
    public function getYearsOfExperience(): int { return $this->yearsOfExperience; }
    public function getCertificationDate(): ?\DateTime { return $this->certificationDate; }
    public function getExpiryDate(): ?\DateTime { return $this->expiryDate; }
    public function getNotes(): string { return $this->notes ?? ''; }

    public function setCertificationDate(\DateTime $date): self
    {
        $this->certificationDate = $date;
        return $this;
    }

    public function setExpiryDate(\DateTime $date): self
    {
        $this->expiryDate = $date;
        return $this;
    }

    public function setNotes(string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }
}

/**
 * Employee Training Entity
 */
class EmployeeTraining
{
    private string $employeeId;
    private string $courseName;
    private \DateTime $startDate;
    private ?\DateTime $endDate;
    private float $durationHours;
    private string $trainer;
    private float $cost;
    private string $status;
    private string $notes;

    public function __construct(
        string $employeeId,
        string $courseName,
        \DateTime $startDate,
        float $durationHours,
        string $status
    ) {
        $this->employeeId = $employeeId;
        $this->courseName = $courseName;
        $this->startDate = $startDate;
        $this->durationHours = $durationHours;
        $this->status = $status;
    }

    public function getEmployeeId(): string { return $this->employeeId; }
    public function getCourseName(): string { return $this->courseName; }
    public function getStartDate(): \DateTime { return $this->startDate; }
    public function getEndDate(): ?\DateTime { return $this->endDate; }
    public function getDurationHours(): float { return $this->durationHours; }
    public function getTrainer(): string { return $this->trainer ?? ''; }
    public function getCost(): float { return $this->cost; }
    public function getStatus(): string { return $this->status; }
    public function getNotes(): string { return $this->notes ?? ''; }

    public function setEndDate(\DateTime $date): self
    {
        $this->endDate = $date;
        return $this;
    }

    public function setTrainer(string $trainer): self
    {
        $this->trainer = $trainer;
        return $this;
    }

    public function setCost(float $cost): self
    {
        $this->cost = $cost;
        return $this;
    }

    public function setNotes(string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }
}