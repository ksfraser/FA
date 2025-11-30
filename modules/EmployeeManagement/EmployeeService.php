<?php
/**
 * FrontAccounting Employee Management Module
 *
 * Comprehensive HR management system with OrangeHRM-like capabilities.
 *
 * @package FA\Modules\EmployeeManagement
 * @version 1.0.0
 * @author FrontAccounting Team
 * @license GPL-3.0
 */

namespace FA\Modules\EmployeeManagement;

use FA\Events\EventDispatcherInterface;
use FA\Database\DBALInterface;
use FA\Services\InventoryService;
use Psr\Log\LoggerInterface;

/**
 * Employee Service
 *
 * Handles employee management, skills, training, and performance tracking
 */
class EmployeeService
{
    private DBALInterface $db;
    private EventDispatcherInterface $events;
    private LoggerInterface $logger;

    public function __construct(
        DBALInterface $db,
        EventDispatcherInterface $events,
        LoggerInterface $logger
    ) {
        $this->db = $db;
        $this->events = $events;
        $this->logger = $logger;
    }

    /**
     * Create a new employee
     *
     * @param array $employeeData Employee information
     * @return Employee The created employee
     * @throws EmployeeException
     */
    public function createEmployee(array $employeeData): Employee
    {
        $this->logger->info('Creating new employee', ['name' => $employeeData['firstName'] ?? '']);

        $this->validateEmployeeData($employeeData);

        $employeeId = $this->getNextEmployeeId();

        $employee = new Employee(
            $employeeId,
            $employeeData['firstName'],
            $employeeData['lastName'],
            $employeeData['email'] ?? '',
            $employeeData['hireDate'] ?? new \DateTime()
        );

        // Set optional fields
        if (isset($employeeData['middleName'])) {
            $employee->setMiddleName($employeeData['middleName']);
        }
        if (isset($employeeData['employeeNumber'])) {
            $employee->setEmployeeNumber($employeeData['employeeNumber']);
        }
        if (isset($employeeData['department'])) {
            $employee->setDepartment($employeeData['department']);
        }
        if (isset($employeeData['jobTitle'])) {
            $employee->setJobTitle($employeeData['jobTitle']);
        }
        if (isset($employeeData['managerId'])) {
            $employee->setManagerId($employeeData['managerId']);
        }
        if (isset($employeeData['status'])) {
            $employee->setStatus($employeeData['status']);
        }

        // Save to database
        $this->saveEmployee($employee);

        // Create default employee profile
        $this->createEmployeeProfile($employee);

        $this->events->dispatch(new EmployeeCreatedEvent($employee));

        $this->logger->info('Employee created successfully', ['employeeId' => $employeeId]);

        return $employee;
    }

    /**
     * Add skill/competency to employee
     *
     * @param string $employeeId Employee ID
     * @param array $skillData Skill information
     * @throws EmployeeException
     */
    public function addEmployeeSkill(string $employeeId, array $skillData): void
    {
        $this->logger->info('Adding skill to employee', [
            'employeeId' => $employeeId,
            'skill' => $skillData['skillName'] ?? ''
        ]);

        $this->validateEmployeeExists($employeeId);
        $this->validateSkillData($skillData);

        $skill = new EmployeeSkill(
            $employeeId,
            $skillData['skillName'],
            $skillData['proficiencyLevel'] ?? 1,
            $skillData['yearsOfExperience'] ?? 0
        );

        if (isset($skillData['certificationDate'])) {
            $skill->setCertificationDate(new \DateTime($skillData['certificationDate']));
        }
        if (isset($skillData['expiryDate'])) {
            $skill->setExpiryDate(new \DateTime($skillData['expiryDate']));
        }
        if (isset($skillData['notes'])) {
            $skill->setNotes($skillData['notes']);
        }

        $this->saveEmployeeSkill($skill);

        $this->events->dispatch(new EmployeeSkillAddedEvent($skill));
    }

    /**
     * Record employee training
     *
     * @param string $employeeId Employee ID
     * @param array $trainingData Training information
     * @throws EmployeeException
     */
    public function recordEmployeeTraining(string $employeeId, array $trainingData): void
    {
        $this->logger->info('Recording employee training', [
            'employeeId' => $employeeId,
            'course' => $trainingData['courseName'] ?? ''
        ]);

        $this->validateEmployeeExists($employeeId);
        $this->validateTrainingData($trainingData);

        $training = new EmployeeTraining(
            $employeeId,
            $trainingData['courseName'],
            new \DateTime($trainingData['startDate']),
            $trainingData['durationHours'] ?? 0,
            $trainingData['status'] ?? 'Completed'
        );

        if (isset($trainingData['endDate'])) {
            $training->setEndDate(new \DateTime($trainingData['endDate']));
        }
        if (isset($trainingData['trainer'])) {
            $training->setTrainer($trainingData['trainer']);
        }
        if (isset($trainingData['cost'])) {
            $training->setCost((float)$trainingData['cost']);
        }
        if (isset($trainingData['notes'])) {
            $training->setNotes($trainingData['notes']);
        }

        $this->saveEmployeeTraining($training);

        $this->events->dispatch(new EmployeeTrainingRecordedEvent($training));
    }

    /**
     * Get employee by ID
     *
     * @param string $employeeId Employee ID
     * @return Employee
     * @throws EmployeeException
     */
    public function getEmployee(string $employeeId): Employee
    {
        $sql = "SELECT * FROM employees WHERE employee_id = ?";
        $result = $this->db->fetchAssoc($sql, [$employeeId]);

        if (!$result) {
            throw new EmployeeException("Employee {$employeeId} not found");
        }

        $employee = new Employee(
            $result['employee_id'],
            $result['first_name'],
            $result['last_name'],
            $result['email'],
            new \DateTime($result['hire_date'])
        );

        $employee->setMiddleName($result['middle_name'] ?? '');
        $employee->setEmployeeNumber($result['employee_number'] ?? '');
        $employee->setDepartment($result['department'] ?? '');
        $employee->setJobTitle($result['job_title'] ?? '');
        $employee->setManagerId($result['manager_id'] ?? '');
        $employee->setStatus($result['status'] ?? 'Active');

        return $employee;
    }

    /**
     * Get employee skills
     *
     * @param string $employeeId Employee ID
     * @return EmployeeSkill[]
     */
    public function getEmployeeSkills(string $employeeId): array
    {
        $sql = "SELECT * FROM employee_skills WHERE employee_id = ? ORDER BY skill_name";
        $results = $this->db->fetchAll($sql, [$employeeId]);

        $skills = [];
        foreach ($results as $result) {
            $skill = new EmployeeSkill(
                $result['employee_id'],
                $result['skill_name'],
                (int)$result['proficiency_level'],
                (int)$result['years_experience']
            );

            if ($result['certification_date']) {
                $skill->setCertificationDate(new \DateTime($result['certification_date']));
            }
            if ($result['expiry_date']) {
                $skill->setExpiryDate(new \DateTime($result['expiry_date']));
            }
            $skill->setNotes($result['notes'] ?? '');

            $skills[] = $skill;
        }

        return $skills;
    }

    /**
     * Get employee training history
     *
     * @param string $employeeId Employee ID
     * @return EmployeeTraining[]
     */
    public function getEmployeeTraining(string $employeeId): array
    {
        $sql = "SELECT * FROM employee_training WHERE employee_id = ? ORDER BY start_date DESC";
        $results = $this->db->fetchAll($sql, [$employeeId]);

        $trainings = [];
        foreach ($results as $result) {
            $training = new EmployeeTraining(
                $result['employee_id'],
                $result['course_name'],
                new \DateTime($result['start_date']),
                (float)$result['duration_hours'],
                $result['status']
            );

            if ($result['end_date']) {
                $training->setEndDate(new \DateTime($result['end_date']));
            }
            $training->setTrainer($result['trainer'] ?? '');
            $training->setCost((float)($result['cost'] ?? 0));
            $training->setNotes($result['notes'] ?? '');

            $trainings[] = $training;
        }

        return $trainings;
    }

    /**
     * Get employees by criteria
     *
     * @param array $filters Filters (department, status, manager, etc.)
     * @return Employee[]
     */
    public function getEmployees(array $filters = []): array
    {
        $sql = "SELECT * FROM employees WHERE 1=1";
        $params = [];
        $conditions = [];

        if (isset($filters['department'])) {
            $conditions[] = "department = ?";
            $params[] = $filters['department'];
        }

        if (isset($filters['status'])) {
            $conditions[] = "status = ?";
            $params[] = $filters['status'];
        }

        if (isset($filters['managerId'])) {
            $conditions[] = "manager_id = ?";
            $params[] = $filters['managerId'];
        }

        if (isset($filters['jobTitle'])) {
            $conditions[] = "job_title = ?";
            $params[] = $filters['jobTitle'];
        }

        $sql .= " " . implode(" AND ", $conditions);
        $sql .= " ORDER BY last_name, first_name";

        $results = $this->db->fetchAll($sql, $params);

        $employees = [];
        foreach ($results as $result) {
            $employee = new Employee(
                $result['employee_id'],
                $result['first_name'],
                $result['last_name'],
                $result['email'],
                new \DateTime($result['hire_date'])
            );

            $employee->setMiddleName($result['middle_name'] ?? '');
            $employee->setEmployeeNumber($result['employee_number'] ?? '');
            $employee->setDepartment($result['department'] ?? '');
            $employee->setJobTitle($result['job_title'] ?? '');
            $employee->setManagerId($result['manager_id'] ?? '');
            $employee->setStatus($result['status'] ?? 'Active');

            $employees[] = $employee;
        }

        return $employees;
    }

    /**
     * Validate employee data
     *
     * @param array $data
     * @throws EmployeeException
     */
    private function validateEmployeeData(array $data): void
    {
        if (empty($data['firstName']) || empty($data['lastName'])) {
            throw new EmployeeException("First name and last name are required");
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new EmployeeException("Invalid email format");
        }

        // Check for duplicate employee number if provided
        if (!empty($data['employeeNumber'])) {
            $sql = "SELECT COUNT(*) as count FROM employees WHERE employee_number = ?";
            $result = $this->db->fetchAssoc($sql, [$data['employeeNumber']]);
            if ($result['count'] > 0) {
                throw new EmployeeException("Employee number already exists");
            }
        }
    }

    /**
     * Validate skill data
     *
     * @param array $data
     * @throws EmployeeException
     */
    private function validateSkillData(array $data): void
    {
        if (empty($data['skillName'])) {
            throw new EmployeeException("Skill name is required");
        }

        if (isset($data['proficiencyLevel']) &&
            ($data['proficiencyLevel'] < 1 || $data['proficiencyLevel'] > 5)) {
            throw new EmployeeException("Proficiency level must be between 1 and 5");
        }
    }

    /**
     * Validate training data
     *
     * @param array $data
     * @throws EmployeeException
     */
    private function validateTrainingData(array $data): void
    {
        if (empty($data['courseName'])) {
            throw new EmployeeException("Course name is required");
        }

        if (empty($data['startDate'])) {
            throw new EmployeeException("Start date is required");
        }
    }

    /**
     * Validate employee exists
     *
     * @param string $employeeId
     * @throws EmployeeException
     */
    private function validateEmployeeExists(string $employeeId): void
    {
        $sql = "SELECT employee_id FROM employees WHERE employee_id = ?";
        $result = $this->db->fetchAssoc($sql, [$employeeId]);

        if (!$result) {
            throw new EmployeeException("Employee {$employeeId} not found");
        }
    }

    /**
     * Get next employee ID
     *
     * @return string
     */
    private function getNextEmployeeId(): string
    {
        $sql = "SELECT MAX(CAST(employee_id AS UNSIGNED)) + 1 as next_id FROM employees";
        $result = $this->db->fetchAssoc($sql);

        return (string)($result['next_id'] ?? 1);
    }

    /**
     * Save employee to database
     *
     * @param Employee $employee
     */
    private function saveEmployee(Employee $employee): void
    {
        $sql = "INSERT INTO employees (
                    employee_id, first_name, last_name, middle_name, email,
                    employee_number, department, job_title, manager_id,
                    hire_date, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $this->db->executeUpdate($sql, [
            $employee->getEmployeeId(),
            $employee->getFirstName(),
            $employee->getLastName(),
            $employee->getMiddleName(),
            $employee->getEmail(),
            $employee->getEmployeeNumber(),
            $employee->getDepartment(),
            $employee->getJobTitle(),
            $employee->getManagerId(),
            $employee->getHireDate()->format('Y-m-d'),
            $employee->getStatus()
        ]);
    }

    /**
     * Create default employee profile
     *
     * @param Employee $employee
     */
    private function createEmployeeProfile(Employee $employee): void
    {
        $sql = "INSERT INTO employee_profiles (employee_id) VALUES (?)";
        $this->db->executeUpdate($sql, [$employee->getEmployeeId()]);
    }

    /**
     * Save employee skill
     *
     * @param EmployeeSkill $skill
     */
    private function saveEmployeeSkill(EmployeeSkill $skill): void
    {
        $sql = "INSERT INTO employee_skills (
                    employee_id, skill_name, proficiency_level, years_experience,
                    certification_date, expiry_date, notes
                ) VALUES (?, ?, ?, ?, ?, ?, ?)";

        $this->db->executeUpdate($sql, [
            $skill->getEmployeeId(),
            $skill->getSkillName(),
            $skill->getProficiencyLevel(),
            $skill->getYearsOfExperience(),
            $skill->getCertificationDate()?->format('Y-m-d'),
            $skill->getExpiryDate()?->format('Y-m-d'),
            $skill->getNotes()
        ]);
    }

    /**
     * Save employee training
     *
     * @param EmployeeTraining $training
     */
    private function saveEmployeeTraining(EmployeeTraining $training): void
    {
        $sql = "INSERT INTO employee_training (
                    employee_id, course_name, start_date, end_date, duration_hours,
                    trainer, cost, status, notes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $this->db->executeUpdate($sql, [
            $training->getEmployeeId(),
            $training->getCourseName(),
            $training->getStartDate()->format('Y-m-d'),
            $training->getEndDate()?->format('Y-m-d'),
            $training->getDurationHours(),
            $training->getTrainer(),
            $training->getCost(),
            $training->getStatus(),
            $training->getNotes()
        ]);
    }
}