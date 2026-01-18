# FrontAccounting AI Coding Guidelines

## Architecture Overview
FrontAccounting is a PHP 8.1+ ERP system with modular automation capabilities. Core system uses Services (business logic), Repositories (data access), and Views (HTML rendering) with SOLID principles. Four automation modules (Scheduler, CRM, Todo, Marketing) built with SuiteCRM-inspired patterns: Bean system with CRUD hooks, WorkflowEngine for automation, and TaskProvider integration for scheduled jobs.

**Module Structure:**
- `modules/{CRM,Todo,Marketing}/` - Standalone PSR-4 modules with own composer.json
- `includes/` - Core FA services and legacy wrappers
- `includes/Integration/` - ModuleBootstrapper wiring all modules together

**Key Architectural Decisions:**
- Bean Pattern (SuiteCRM-style): `AbstractBean` with change tracking, validation, and CRUD hooks (before_save, after_save, before_delete, after_delete, after_retrieve)
- Hook System: Priority-based execution with propagation control (return false stops execution)
- Repository Pattern: All data access through repositories with hook integration points
- Cross-Module Workflows: Won opportunity → creates celebration task (CRM → Todo integration)

## Key Patterns

### Bean System (CRM Module)
```php
// All entities extend AbstractBean
class Lead extends AbstractBean {
    public function getModuleName(): string { return 'Leads'; }
    public function isHot(): bool { return $this->score >= 75; }
    public function validate(): array { /* Symfony Validator */ }
}

// Hooks execute at CRUD operations
$hookManager->registerHook('Leads', 'before_save', function($event) {
    $lead = $event->getBean();
    $lead->score = calculateLeadScore($lead); // Modify before save
}, 10); // Priority 10
```

### Repositories with Hook Integration
```php
class BeanRepository {
    public function save(BeanInterface $bean): void {
        $this->hookManager->executeHooks($bean->getModuleName(), 'before_save', ...);
        // Validate
        $isNew = $bean->isNew();
        // INSERT or UPDATE only changed fields
        $this->hookManager->executeHooks($bean->getModuleName(), 'after_save', ...);
    }
}
```

### Workflow Automation (WorkflowEngine)
```php
// CRM workflows - registered in constructor
$this->workflows = [
    'calculateLeadScore' => fn($lead) => /* scoring logic */,
    'scheduleLeadFollowUp' => fn($lead) => /* hot leads get 1 day, normal 3 days */,
    'autoConvertQualifiedLead' => fn($lead) => /* score >= 80 → Opportunity */
];
```

### Scheduler Integration (TaskProvider Pattern)
```php
class CRMTaskProvider {
    public function getAvailableTaskTypes(): array {
        return ['lead_followup', 'stage_change_notification', ...];
    }
    
    public function executeTask(string $taskType, array $params): array {
        return match($taskType) {
            'lead_followup' => $this->executeFollowUp($params),
            ...
        };
    }
}
```

## Development Workflow

### TDD Process
1. Write test first in `modules/{Module}/tests/`
2. Use Mockery for dependency mocking
3. Run tests: `cd modules/{Module} && vendor/bin/phpunit`
4. Implement feature
5. Verify 100% coverage

### Creating New Modules
```bash
mkdir -p modules/NewModule/src/{Entity,Service,Repository,Task}
# composer.json with PSR-4: "FA\\NewModule\\": "src/"
# Add ksfraser/html, ksfraser/exceptions, ksfraser/prefs dependencies
```

### Wiring to Scheduler
```php
// In ModuleBootstrapper
public function registerNewModule(): void {
    $provider = new NewModuleTaskProvider(...);
    $this->registry->register('new_module', $provider);
    
    // Schedule recurring jobs
    $this->scheduler->schedule('check_something', '0 8 * * *', [
        'type' => 'new_module',
        'task' => 'daily_check'
    ]);
}
```

## KSFraser Libraries Integration

### UI Components (ksfraser/html)
```php
// FA-compatible forms
use Ksfraser\HTML\FaUiFunctions;
FaUiFunctions::start_form();
FaUiFunctions::text_row('Name', 'name', $value, 30, 50);
FaUiFunctions::submit_center('submit', 'Save');

// Modern HTML5 components
use Ksfraser\HTML\Elements\{Form, Input, Button};
$form = (new Form())->setMethod('post')->setAction('/submit');
$input = (new Input('text'))->setName('name')->addClass('form-control');
echo $form->render();
```

### Exception Handling (ksfraser/exceptions)
```php
throw new LeadNotFoundException($leadId); // 404
throw new ValidationException($errors); // 400 with field errors
```

### Preferences (ksfraser/prefs)
```php
$prefs = new CRMPreferences();
$threshold = $prefs->get('hot_lead_threshold', 75);
$prefs->set('auto_convert_enabled', true);
$prefs->save();
```

## Module-Specific Patterns

### CRM: Lead Scoring Algorithm
```php
$score = 0;
if ($lead->email) $score += 10;
if ($lead->phone) $score += 10;
if ($lead->company) $score += 15;
if ($lead->status === 'contacted') $score += 20;
if ($lead->source === 'referral') $score += 25;
if ($lead->budget > 10000) $score += 20;
// Total: 0-100 score
```

### Marketing: Campaign Stats
```php
$stats = $campaign->getStats();
$openRate = $stats->getOpenRate(); // (opened / delivered) * 100
$clickRate = $stats->getClickRate(); // (clicked / delivered) * 100
$bounceRate = $stats->getBounceRate(); // (bounced / sent) * 100
```

### Todo: Progress Tracking
```php
// Expected vs actual with 10% tolerance
$expected = ($elapsedDays / $totalDays) * 100;
$isBehind = ($goal->currentValue < $goal->targetValue * ($expected - 10) / 100);
```

## Data Flows

**Campaign Send Flow:**
1. AutomationService::sendScheduledCampaigns() (scheduled task)
2. CampaignRepository::findDueForSending()
3. Send via Symfony Mailer / external API
4. Update CampaignStats (sent, delivered, opened, clicked)

**Lead Conversion Flow:**
1. Lead created → WorkflowEngine::calculateLeadScore()
2. Score calculated → WorkflowEngine::scheduleLeadFollowUp()
3. If qualified (score >= 80) → WorkflowEngine::autoConvertQualifiedLead()
4. Create Opportunity bean → Save with hooks
5. Opportunity won → Cross-module hook → Todo creates celebration task

## Testing Commands
```bash
# All tests
php run-tests.php

# Specific module
cd modules/CRM && vendor/bin/phpunit
cd modules/Marketing && vendor/bin/phpunit --testdox

# Coverage
vendor/bin/phpunit --coverage-html coverage/
```

## Database Conventions
- All tables use InnoDB engine
- Primary keys: VARCHAR(36) for UUIDs (e.g., 'camp_uniqid', 'lead_uniqid')
- JSON columns for metadata, criteria, arrays
- Created_at/updated_at timestamps on all tables
- Foreign keys with CASCADE on delete

## Cross-Module Communication
```php
// Register cross-module workflow in ModuleBootstrapper
$this->hookManager->registerHook('Opportunities', 'after_save', function($event) {
    $opp = $event->getBean();
    if ($opp->isWon()) {
        $this->createCelebrationTask($opp); // CRM → Todo
    }
});
```

## Odoo/SuiteCRM/webERP Inspirations
- **Bean System**: SuiteCRM's bean architecture with pre/post CRUD triggers
- **Workflow Engine**: Odoo's automated actions based on record changes
- **Module Structure**: Independent modules with own dependencies
- **Hook Priority**: SuiteCRM's priority-based hook execution
- **Repository Pattern**: Clean separation like Odoo's ORM

## Key Files by Module

### Core Integration
- `includes/Integration/ModuleBootstrapper.php` - Central wiring, cross-module workflows, recurring jobs

### CRM Module  
- `modules/CRM/src/Bean/AbstractBean.php` - Base bean with change tracking
- `modules/CRM/src/Hook/HookManager.php` - Priority-based hook system
- `modules/CRM/src/Workflow/WorkflowEngine.php` - 5 automation workflows
- `modules/CRM/src/UI/LeadFormBuilder.php` - Dual FA/modern form generation

### Marketing Module
- `modules/Marketing/src/Entity/Campaign.php` - Campaign entity with stats
- `modules/Marketing/src/Service/DripCampaignService.php` - Email sequences
- `modules/Marketing/src/Service/AutomationService.php` - Scheduled sends, A/B testing

### Todo Module
- `modules/Todo/src/Entity/Task.php` - Task with progress tracking
- `modules/Todo/src/Service/ReminderService.php` - Smart alerting (overdue, upcoming, goal progress)