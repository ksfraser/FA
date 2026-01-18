# KSFraser Libraries Integration Guide

## Overview

All modules now integrate with three KSFraser libraries:
- **ksfraser/exceptions** - Standardized exception handling
- **ksfraser/prefs** - Preferences and configuration management
- **ksfraser/html** - UI component library with FA integration

## Installation

Add to each module's `composer.json`:

```json
"require": {
    "ksfraser/exceptions": "*",
    "ksfraser/prefs": "*",
    "ksfraser/html": "*"
}
```

Then run:
```bash
composer update
```

## Usage Examples

### 1. ksfraser/HTML - UI Components

#### Modern HTML5 Forms

```php
use Ksfraser\HTML\Elements\Form;
use Ksfraser\HTML\Elements\Input;
use Ksfraser\HTML\Elements\Button;

$form = (new Form())
    ->setMethod('post')
    ->setAction('/submit.php')
    ->addClass('modern-form');

$nameInput = (new Input('text'))
    ->setName('name')
    ->setAttribute('required', 'required')
    ->addClass('form-control');

$submitBtn = (new Button())
    ->setText('Save')
    ->setType('submit')
    ->addClass('btn-primary');

$form->append($nameInput->render());
$form->append($submitBtn->render());

echo $form->render();
```

#### FA-Style Forms (Legacy Compatibility)

```php
use Ksfraser\HTML\FaUiFunctions;

FaUiFunctions::start_form();
FaUiFunctions::start_table(FaUiFunctions::TABLESTYLE2);

FaUiFunctions::text_row('Name', 'name', '', 30, 50);
FaUiFunctions::email_row('Email', 'email', '', 30, 100);
FaUiFunctions::date_row('Date', 'date', '', null, 0, 0, 0, null, false);

FaUiFunctions::end_table(1);
FaUiFunctions::submit_center('submit', 'Save');
FaUiFunctions::end_form();
```

#### Tables with Actions

```php
use Ksfraser\HTML\Elements\HtmlTable;
use Ksfraser\HTML\Elements\TableRow;
use Ksfraser\HTML\Elements\Button;

$table = HtmlTable::createFaTable(2, 'width=100%');

// Header
$headerRow = new TableRow();
foreach (['ID', 'Name', 'Actions'] as $header) {
    $th = new TableData();
    $th->setText($header);
    $headerRow->append($th->getHtml());
}
$table->appendChild($headerRow);

// Data rows
foreach ($items as $item) {
    $row = new TableRow();
    
    $idCell = new TableData();
    $idCell->setText($item['id']);
    $row->append($idCell->getHtml());
    
    $nameCell = new TableData();
    $nameCell->setText($item['name']);
    $row->append($nameCell->getHtml());
    
    $actionsCell = new TableData();
    $editBtn = (new Button())
        ->setText('Edit')
        ->setType('button')
        ->setAttribute('onclick', "editItem({$item['id']})");
    $actionsCell->setText($editBtn->render());
    $row->append($actionsCell->getHtml());
    
    $table->appendChild($row);
}

echo $table->getHtml();
```

### 2. ksfraser/Exceptions - Error Handling

#### Custom Exceptions

```php
namespace FA\CRM\Exception;

class LeadNotFoundException extends \Exception
{
    public function __construct(string $leadId)
    {
        parent::__construct("Lead not found: {$leadId}", 404);
    }
}

class ValidationException extends \Exception
{
    private array $errors;
    
    public function __construct(array $errors)
    {
        $this->errors = $errors;
        parent::__construct('Validation failed', 400);
    }
    
    public function getErrors(): array
    {
        return $this->errors;
    }
}
```

#### Using Exceptions

```php
use FA\CRM\Exception\LeadNotFoundException;
use FA\CRM\Exception\ValidationException;

try {
    $lead = $leadRepo->findById($id);
    if (!$lead) {
        throw new LeadNotFoundException($id);
    }
    
    $errors = $lead->validate();
    if (!empty($errors)) {
        throw new ValidationException($errors);
    }
    
    $leadRepo->save($lead);
} catch (LeadNotFoundException $e) {
    // Handle not found - show 404 page
    http_response_code(404);
    echo "Lead not found";
} catch (ValidationException $e) {
    // Handle validation errors - show form with errors
    foreach ($e->getErrors() as $field => $error) {
        echo "<p class='error'>$field: $error</p>";
    }
}
```

### 3. ksfraser/Prefs - Preferences Management

#### Define Preferences

```php
namespace FA\CRM\Preferences;

class CRMPreferences
{
    // Constants
    public const HOT_LEAD_THRESHOLD = 75;
    public const AUTO_CONVERT_ENABLED = true;
    
    private array $settings = [];
    
    public function get(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }
    
    public function set(string $key, $value): void
    {
        $this->settings[$key] = $value;
    }
    
    public function save(): void
    {
        // Persist to database
    }
}
```

#### Using Preferences

```php
use FA\CRM\Preferences\CRMPreferences;

$prefs = new CRMPreferences();

// Get settings
$threshold = $prefs->get('hot_lead_threshold', 75);
$autoConvert = $prefs->get('auto_convert_enabled', true);

// Modify settings
$prefs->set('hot_lead_threshold', 80);
$prefs->save();

// Use in workflows
if ($lead->score >= $threshold) {
    // Lead is hot
    if ($autoConvert && $lead->isQualified()) {
        $this->convertToOpportunity($lead);
    }
}
```

## Module-Specific Integration

### CRM Module

**Files Created:**
- `src/UI/LeadFormBuilder.php` - Form generation with both modern and FA styles
- `src/UI/LeadListView.php` - Table view with action buttons
- `src/Exception/LeadNotFoundException.php` - Custom exception
- `src/Exception/ValidationException.php` - Validation errors
- `src/Preferences/CRMPreferences.php` - Settings management

**Usage:**
```php
// Display lead form (FA style)
$formBuilder = new LeadFormBuilder();
$formBuilder->buildFaForm($lead);

// Display leads list
$listView = new LeadListView();
echo $listView->render($leads);

// Handle exceptions
try {
    $lead = $leadRepo->findById($id);
} catch (LeadNotFoundException $e) {
    display_error($e->getMessage());
}
```

### Marketing Module

**Files Created:**
- `src/UI/CampaignFormBuilder.php` - Campaign forms with both styles

**Usage:**
```php
// FA-style campaign form
$formBuilder = new CampaignFormBuilder();
$formBuilder->buildFaForm($campaign);

// Modern HTML5 form
echo $formBuilder->buildModernForm($campaign);
```

### Todo Module

Similar patterns apply:
- Use `ksfraser/HTML` for task forms and lists
- Use `ksfraser/Exceptions` for TaskNotFoundException
- Use `ksfraser/Prefs` for reminder settings

## Benefits

### 1. Code Reusability
- Consistent UI components across modules
- Shared exception handling patterns
- Centralized preferences management

### 2. Maintainability
- Changes to UI library automatically benefit all modules
- Consistent error handling reduces debugging
- Settings changes in one place

### 3. FA Integration
- `FaUiFunctions` provides seamless FA compatibility
- Existing FA code works without changes
- Gradual migration to modern components

### 4. Type Safety
- Strong typing in all components
- IDE autocomplete support
- Fewer runtime errors

## Migration Strategy

### Phase 1: Add Dependencies (COMPLETE)
✅ Updated all `composer.json` files
✅ Added example implementations

### Phase 2: Implement UI Components
- Replace procedural FA UI calls with `FaUiFunctions` wrapper
- Build forms using `LeadFormBuilder`, `CampaignFormBuilder`
- Create table views using `HtmlTable` components

### Phase 3: Add Exception Handling
- Replace generic exceptions with domain-specific ones
- Add try-catch blocks in controllers
- Display user-friendly error messages

### Phase 4: Implement Preferences
- Move hard-coded settings to `Preferences` classes
- Add admin UI for settings management
- Persist settings to database

## Next Steps

1. **Run composer update** in each module:
   ```bash
   cd modules/CRM && composer update
   cd modules/Marketing && composer update
   cd modules/Todo && composer update
   ```

2. **Test UI Components**:
   - Create test pages using form builders
   - Verify FA compatibility
   - Test modern HTML5 forms

3. **Implement Exception Handling**:
   - Add try-catch in controllers
   - Display validation errors
   - Log exceptions appropriately

4. **Configure Preferences**:
   - Create admin pages for settings
   - Test preference persistence
   - Use settings in workflows

## Resources

- **ksfraser/HTML**: https://github.com/ksfraser/html
- **ksfraser/Exceptions**: https://github.com/ksfraser/Exceptions
- **ksfraser/Prefs**: https://github.com/ksfraser/Prefs
