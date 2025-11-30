# FrontAccounting HR and Project Management Modules

This document describes the comprehensive HR and Project Management enhancement modules for FrontAccounting, providing OrangeHRM-like and dotProject/OpenProject-like capabilities natively integrated into FA.

## Overview

The HR and Project Management system consists of three main modules:

1. **EmployeeManagement** - Core HR functionality including employee records, skills tracking, and training management
2. **TimesheetManagement** - Time tracking for both employee compensation and project billing
3. **ProjectManagement** - Project task management with resource allocation and progress tracking

These modules are integrated through a unified `HRProjectManagementService` that provides comprehensive HR and project management capabilities.

## Features

### Employee Management (OrangeHRM-like)
- Complete employee lifecycle management
- Skills and competency tracking
- Training records and certifications
- Performance management integration
- Employee status and role management

### Timesheet Management
- Daily time tracking with project/task assignment
- Billable vs non-billable time distinction
- Approval workflows for time entries
- Integration with payroll and billing systems
- Utilization reporting

### Project Management (dotProject/OpenProject-like)
- Project creation with budget and timeline management
- Task breakdown with dependencies
- Resource allocation and team assignment
- Progress tracking and milestone management
- Gantt chart integration potential

### Integration Features
- Unified employee utilization reports
- Project budget vs actual cost tracking
- Cross-module workflow automation
- Comprehensive dashboard and reporting
- Event-driven architecture for extensibility

## Architecture

### Service-Oriented Design
- **EmployeeService** - Handles all employee-related operations
- **TimesheetService** - Manages time tracking and approvals
- **ProjectService** - Coordinates project and task management
- **HRProjectManagementService** - Unified interface for integrated operations

### Event-Driven Architecture
- PSR-14 compatible event system
- Events for employee creation, project assignment, timesheet approval, etc.
- Extensible for custom business logic and integrations

### Database Integration
- Doctrine DBAL interface for database abstraction
- Support for complex queries with joins and transactions
- Optimized for performance with proper indexing

## Installation

### Prerequisites
- PHP 8.0+
- FrontAccounting with PSR-4 autoloading
- Database tables (see schema below)

### Module Structure
```
modules/
├── EmployeeManagement/
│   ├── EmployeeService.php
│   ├── Entities.php
│   ├── Events.php
│   └── EmployeeException.php
├── TimesheetManagement/
│   ├── TimesheetService.php
│   ├── Entities.php
│   ├── Events.php
│   └── TimesheetException.php
├── ProjectManagement/
│   ├── ProjectService.php
│   ├── Entities.php
│   ├── Events.php
│   └── ProjectException.php
└── HRProjectManagementService.php
```

### Database Schema

#### Employees Table
```sql
CREATE TABLE employees (
    employee_id VARCHAR(20) PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    hire_date DATE,
    job_title VARCHAR(100),
    department VARCHAR(100),
    salary DECIMAL(10,2),
    status ENUM('Active', 'Inactive', 'Terminated') DEFAULT 'Active',
    manager_id VARCHAR(20),
    next_review_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### Employee Skills Table
```sql
CREATE TABLE employee_skills (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id VARCHAR(20) NOT NULL,
    skill_name VARCHAR(100) NOT NULL,
    proficiency_level ENUM('Beginner', 'Intermediate', 'Advanced', 'Expert') DEFAULT 'Beginner',
    years_experience INT DEFAULT 0,
    last_used DATE,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
);
```

#### Employee Training Table
```sql
CREATE TABLE employee_training (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id VARCHAR(20) NOT NULL,
    course_name VARCHAR(200) NOT NULL,
    provider VARCHAR(100),
    completion_date DATE,
    expiry_date DATE,
    certification_number VARCHAR(50),
    status ENUM('Completed', 'In Progress', 'Expired') DEFAULT 'Completed',
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
);
```

#### Timesheet Entries Table
```sql
CREATE TABLE timesheet_entries (
    entry_id VARCHAR(20) PRIMARY KEY,
    employee_id VARCHAR(20) NOT NULL,
    project_id VARCHAR(20),
    task_id VARCHAR(20),
    entry_date DATE NOT NULL,
    hours DECIMAL(5,2) NOT NULL,
    description TEXT,
    is_billable BOOLEAN DEFAULT FALSE,
    billing_rate DECIMAL(8,2) DEFAULT 0,
    status ENUM('Draft', 'Pending', 'Approved', 'Rejected') DEFAULT 'Draft',
    submitted_at TIMESTAMP NULL,
    approved_at TIMESTAMP NULL,
    approved_by VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id),
    FOREIGN KEY (project_id) REFERENCES projects(project_id),
    FOREIGN KEY (task_id) REFERENCES project_tasks(task_id)
);
```

#### Projects Table
```sql
CREATE TABLE projects (
    project_id VARCHAR(20) PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    start_date DATE NOT NULL,
    end_date DATE,
    budget DECIMAL(12,2) DEFAULT 0,
    customer_id VARCHAR(20),
    project_manager VARCHAR(20) NOT NULL,
    priority ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Medium',
    status ENUM('Planning', 'In Progress', 'On Hold', 'Completed', 'Cancelled') DEFAULT 'Planning',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_manager) REFERENCES employees(employee_id)
);
```

#### Project Tasks Table
```sql
CREATE TABLE project_tasks (
    task_id VARCHAR(20) PRIMARY KEY,
    project_id VARCHAR(20) NOT NULL,
    parent_task_id VARCHAR(20),
    name VARCHAR(200) NOT NULL,
    description TEXT,
    assigned_to VARCHAR(20),
    start_date DATE,
    end_date DATE,
    estimated_hours DECIMAL(6,2) DEFAULT 0,
    actual_hours DECIMAL(6,2) DEFAULT 0,
    progress DECIMAL(5,2) DEFAULT 0,
    priority ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Medium',
    status ENUM('Not Started', 'In Progress', 'On Hold', 'Completed', 'Cancelled') DEFAULT 'Not Started',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE,
    FOREIGN KEY (parent_task_id) REFERENCES project_tasks(task_id),
    FOREIGN KEY (assigned_to) REFERENCES employees(employee_id)
);
```

#### Project Assignments Table
```sql
CREATE TABLE project_assignments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id VARCHAR(20) NOT NULL,
    employee_id VARCHAR(20) NOT NULL,
    role VARCHAR(100) DEFAULT 'Team Member',
    start_date DATE NOT NULL,
    end_date DATE,
    allocation_percentage DECIMAL(5,2) DEFAULT 100.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (project_id, employee_id, start_date)
);
```

## Usage Examples

### Creating an Employee Profile

```php
use FA\Modules\HRProjectManagementService;

// Initialize service (dependency injection container would handle this)
$hrService = new HRProjectManagementService(/* dependencies */);

// Create complete employee profile
$employeeId = $hrService->createEmployeeProfile(
    [
        'firstName' => 'John',
        'lastName' => 'Doe',
        'email' => 'john.doe@company.com',
        'hireDate' => '2024-01-15',
        'jobTitle' => 'Software Developer',
        'department' => 'IT',
        'salary' => 75000.00
    ],
    [
        ['skillName' => 'PHP', 'proficiencyLevel' => 'Advanced', 'yearsExperience' => 5],
        ['skillName' => 'JavaScript', 'proficiencyLevel' => 'Expert', 'yearsExperience' => 7]
    ],
    [
        ['courseName' => 'Advanced PHP Development', 'completionDate' => '2023-12-01'],
        ['courseName' => 'Project Management', 'completionDate' => '2023-11-15']
    ]
);
```

### Creating a Project with Team

```php
$projectId = $hrService->createProjectWithTeam(
    [
        'name' => 'Website Redesign',
        'description' => 'Complete redesign of company website',
        'startDate' => '2024-02-01',
        'endDate' => '2024-05-31',
        'budget' => 50000.00,
        'projectManager' => 'EMP001'
    ],
    [
        ['employeeId' => 'EMP002', 'role' => 'Lead Developer', 'allocationPercentage' => 100],
        ['employeeId' => 'EMP003', 'role' => 'UI/UX Designer', 'allocationPercentage' => 75]
    ],
    [
        ['name' => 'Requirements Gathering', 'estimatedHours' => 40, 'priority' => 'High'],
        ['name' => 'Design Phase', 'estimatedHours' => 80, 'priority' => 'High'],
        ['name' => 'Development', 'estimatedHours' => 200, 'priority' => 'Medium']
    ]
);
```

### Recording Time and Getting Reports

```php
// Record time worked
$entryId = $hrService->recordWorkTime([
    'employeeId' => 'EMP002',
    'projectId' => $projectId,
    'taskId' => 'TASK001',
    'entryDate' => '2024-02-15',
    'hours' => 8.0,
    'description' => 'Completed requirements gathering',
    'isBillable' => true,
    'billingRate' => 125.00
]);

// Get employee utilization report
$utilization = $hrService->getEmployeeUtilization(
    'EMP002',
    new DateTime('2024-02-01'),
    new DateTime('2024-02-28')
);

// Get project progress
$progress = $hrService->getProjectProgress($projectId);

// Get HR dashboard data
$dashboard = $hrService->getHRDashboard();
```

## API Reference

### HRProjectManagementService Methods

#### Employee Operations
- `createEmployeeProfile(array $employeeData, array $skills = [], array $training = []): string`
- `getEmployeeUtilization(string $employeeId, DateTime $startDate, DateTime $endDate): array`

#### Project Operations
- `createProjectWithTeam(array $projectData, array $teamMembers = [], array $initialTasks = []): string`
- `getProjectProgress(string $projectId): array`

#### Timesheet Operations
- `recordWorkTime(array $timesheetData): string`

#### Reporting
- `getHRDashboard(): array`

### Individual Service Methods

#### EmployeeService
- `createEmployee(array $data): Employee`
- `getEmployee(string $id): Employee`
- `updateEmployee(string $id, array $data): Employee`
- `addEmployeeSkill(string $employeeId, array $skillData): void`
- `recordEmployeeTraining(string $employeeId, array $trainingData): void`

#### TimesheetService
- `createTimesheetEntry(array $data): TimesheetEntry`
- `submitTimesheet(string $employeeId, DateTime $periodStart, DateTime $periodEnd): void`
- `approveTimesheet(string $entryId, string $approverId): void`
- `getEmployeeTimesheetEntries(string $employeeId, DateTime $start, DateTime $end): array`

#### ProjectService
- `createProject(array $data): Project`
- `createTask(array $data): Task`
- `assignEmployeeToProject(string $projectId, string $employeeId, array $assignmentData): void`
- `updateTaskProgress(string $taskId, float $progress, string $status): void`
- `getProject(string $id): Project`
- `getProjectTasks(string $projectId): array`

## Events

The system uses PSR-14 events for extensibility:

- `EmployeeCreatedEvent` - Fired when employee is created
- `EmployeeSkillAddedEvent` - Fired when skill is added to employee
- `TimesheetEntryCreatedEvent` - Fired when timesheet entry is created
- `TimesheetSubmittedEvent` - Fired when timesheet is submitted for approval
- `ProjectCreatedEvent` - Fired when project is created
- `TaskCreatedEvent` - Fired when task is created
- `EmployeeAssignedToProjectEvent` - Fired when employee is assigned to project
- `TaskProgressUpdatedEvent` - Fired when task progress is updated

## Security Considerations

- All services validate input data and permissions
- Timesheet approvals require appropriate role permissions
- Employee data access is restricted based on user roles
- Database queries use parameterized statements to prevent SQL injection

## Performance Optimization

- Database indexes on frequently queried columns
- Efficient queries with proper JOIN operations
- Lazy loading for related entities where appropriate
- Caching strategies for frequently accessed data

## Future Enhancements

- Gantt chart visualization
- Advanced reporting and analytics
- Integration with calendar systems
- Mobile app API endpoints
- Advanced workflow automation
- Integration with external HR systems

## Support

For support and contributions, please refer to the main FrontAccounting documentation and community forums.