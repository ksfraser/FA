<?php
$page_security = 'SA_CUSTOM_FIELDS';
$path_to_root = "../..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/CustomFields/CustomFieldManager.php");

$page_title = _("Custom Fields Manager");

$fieldManager = \FA\CustomFields\CustomFieldManager::getInstance();

// Handle form submissions
if (isset($_POST['create_field'])) {
    $fieldData = [
        'field_name' => $_POST['field_name'],
        'field_label' => $_POST['field_label'],
        'field_type' => $_POST['field_type'],
        'field_length' => !empty($_POST['field_length']) ? (int) $_POST['field_length'] : null,
        'is_required' => isset($_POST['is_required']),
        'default_value' => $_POST['default_value'] ?? null,
        'display_order' => (int) ($_POST['display_order'] ?? 0),
        'group_id' => !empty($_POST['group_id']) ? (int) $_POST['group_id'] : null,
        'is_active' => isset($_POST['is_active']),
        'created_by' => $_SESSION['wa_current_user']->user
    ];

    // Handle select options
    if (in_array($_POST['field_type'], ['select', 'multiselect'])) {
        $options = [];
        if (!empty($_POST['select_options'])) {
            $lines = explode("\n", trim($_POST['select_options']));
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    // Support key:value format or just value
                    if (strpos($line, ':') !== false) {
                        list($key, $value) = explode(':', $line, 2);
                        $options[trim($key)] = trim($value);
                    } else {
                        $options[$line] = $line;
                    }
                }
            }
        }
        $fieldData['select_options'] = $options;
    }

    try {
        $fieldId = $fieldManager->createField($_POST['entity_type'], $fieldData);
        display_notification(_("Custom field created successfully"));
    } catch (Exception $e) {
        display_error(_("Error creating custom field: ") . $e->getMessage());
    }

    meta_forward($_SERVER['PHP_SELF']);
}

if (isset($_POST['update_field'])) {
    $fieldId = (int) $_POST['field_id'];
    $fieldData = [
        'field_label' => $_POST['field_label'],
        'field_type' => $_POST['field_type'],
        'field_length' => !empty($_POST['field_length']) ? (int) $_POST['field_length'] : null,
        'is_required' => isset($_POST['is_required']),
        'default_value' => $_POST['default_value'] ?? null,
        'display_order' => (int) ($_POST['display_order'] ?? 0),
        'group_id' => !empty($_POST['group_id']) ? (int) $_POST['group_id'] : null,
        'is_active' => isset($_POST['is_active'])
    ];

    // Handle select options
    if (in_array($_POST['field_type'], ['select', 'multiselect'])) {
        $options = [];
        if (!empty($_POST['select_options'])) {
            $lines = explode("\n", trim($_POST['select_options']));
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    if (strpos($line, ':') !== false) {
                        list($key, $value) = explode(':', $line, 2);
                        $options[trim($key)] = trim($value);
                    } else {
                        $options[$line] = $line;
                    }
                }
            }
        }
        $fieldData['select_options'] = $options;
    }

    if ($fieldManager->updateField($fieldId, $fieldData)) {
        display_notification(_("Custom field updated successfully"));
    } else {
        display_error(_("Error updating custom field"));
    }

    meta_forward($_SERVER['PHP_SELF']);
}

if (isset($_GET['delete_field'])) {
    $fieldId = (int) $_GET['delete_field'];

    if ($fieldManager->deleteField($fieldId)) {
        display_notification(_("Custom field deleted successfully"));
    } else {
        display_error(_("Error deleting custom field"));
    }

    meta_forward($_SERVER['PHP_SELF']);
}

include_once($path_to_root . "/includes/ui.inc");

page($page_title);

// Get entity type filter
$entityType = $_GET['entity_type'] ?? 'customers';

// Display entity type selector
$entityTypes = [
    'customers' => _("Customers"),
    'suppliers' => _("Suppliers"),
    'items' => _("Items"),
    'employees' => _("Employees")
];

echo "<div class='entity-selector'>";
echo "<h3>" . _("Select Entity Type") . "</h3>";
echo "<div class='entity-tabs'>";

foreach ($entityTypes as $type => $label) {
    $active = ($type === $entityType) ? 'active' : '';
    echo "<a href='?entity_type={$type}' class='entity-tab {$active}'>{$label}</a>";
}

echo "</div></div>";

// Display custom fields for selected entity type
$fields = $fieldManager->getFieldsForEntity($entityType);

start_table(TABLESTYLE);
$tableheader = array(
    _("Field Name"),
    _("Label"),
    _("Type"),
    _("Required"),
    _("Active"),
    _("Order"),
    _("Actions")
);
table_header($tableheader);

if (empty($fields)) {
    label_cell(_("No custom fields defined for this entity type."), "colspan=7");
} else {
    foreach ($fields as $field) {
        start_row();

        label_cell($field['field_name']);
        label_cell($field['field_label']);
        label_cell(ucfirst($field['field_type']));
        label_cell($field['is_required'] ? _("Yes") : _("No"));
        label_cell($field['is_active'] ? _("Yes") : _("No"));
        label_cell($field['display_order']);
        label_cell(
            "<a href='?entity_type={$entityType}&edit_field={$field['id']}'>" . _("Edit") . "</a> | " .
            "<a href='?entity_type={$entityType}&delete_field={$field['id']}' onclick='return confirm(\"" . _("Are you sure you want to delete this field?") . "\")'>" . _("Delete") . "</a>"
        );

        end_row();
    }
}

end_table(1);

// Create/Edit field form
$editing = false;
$field = null;

if (isset($_GET['edit_field'])) {
    $fieldId = (int) $_GET['edit_field'];
    $field = $fieldManager->getField($fieldId);
    $editing = true;
}

start_form();

if ($editing && $field) {
    echo "<h3>" . _("Edit Custom Field") . "</h3>";
} else {
    echo "<h3>" . _("Create New Custom Field") . "</h3>";
}

hidden('entity_type', $entityType);

if ($editing) {
    hidden('field_id', $field['id']);
}

start_table(TABLESTYLE2);

text_row(_("Field Name:"), 'field_name', $field['field_name'] ?? '', 30, 50);
text_row(_("Field Label:"), 'field_label', $field['field_label'] ?? '', 30, 100);

$fieldTypes = [
    'text' => _("Text"),
    'textarea' => _("Textarea"),
    'number' => _("Number"),
    'decimal' => _("Decimal"),
    'date' => _("Date"),
    'datetime' => _("Date/Time"),
    'boolean' => _("Checkbox"),
    'select' => _("Select"),
    'multiselect' => _("Multi-Select"),
    'email' => _("Email"),
    'url' => _("URL"),
    'phone' => _("Phone")
];

select_row(_("Field Type:"), 'field_type', $field['field_type'] ?? 'text', $fieldTypes);

text_row(_("Field Length:"), 'field_length', $field['field_length'] ?? '', 10, 10);

check_row(_("Required:"), 'is_required', $field['is_required'] ?? false);
check_row(_("Active:"), 'is_active', $field['is_active'] ?? true);

text_row(_("Default Value:"), 'default_value', $field['default_value'] ?? '', 30, 255);
text_row(_("Display Order:"), 'display_order', $field['display_order'] ?? 0, 10, 10);

// Select options for select/multiselect fields
echo "<tr><td class='label'>" . _("Select Options:") . "</td>";
echo "<td><textarea name='select_options' rows='5' cols='40' placeholder='" . _("One option per line. Use key:value format for custom labels.") . "'>";

if ($field && !empty($field['select_options'])) {
    foreach ($field['select_options'] as $key => $value) {
        if ($key === $value) {
            echo htmlspecialchars($key) . "\n";
        } else {
            echo htmlspecialchars($key) . ":" . htmlspecialchars($value) . "\n";
        }
    }
}

echo "</textarea></td></tr>";

end_table(1);

if ($editing) {
    submit_center('update_field', _("Update Field"));
} else {
    submit_center('create_field', _("Create Field"));
}

end_form();

end_page();