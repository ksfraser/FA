<?php
$page_security = 'SA_WORKFLOW_MANAGEMENT';
$path_to_root = '..';

require_once $path_to_root . '/includes/session.inc';
require_once $path_to_root . '/includes/ui.inc';
require_once $path_to_root . '/includes/Services/EventManager.php';
require_once $path_to_root . '/includes/Services/WorkflowService.php';

page(_('Workflow Designer'));

// Handle form submissions
if (isset($_POST['create_workflow'])) {
    // Create a new workflow based on form data
    $eventName = $_POST['event_trigger'];
    $workflowName = $_POST['workflow_name'];

    // Register the workflow
    \FA\Services\WorkflowService::createNotificationWorkflow(
        $eventName,
        ['admin@example.com'], // Default recipients
        "Workflow {$workflowName} triggered for event: {$eventName}"
    );

    display_notification(_('Workflow created successfully'));
}

// Display workflow designer interface
start_form();

start_table(TABLESTYLE);
table_header(array(_('Workflow Name'), _('Trigger Event'), _('Actions')));

// Display existing workflows (simplified for now)
$workflows = [
    ['name' => 'Customer Approval', 'event' => 'FA\\Events\\DatabasePreWriteEvent'],
    ['name' => 'Invoice Notification', 'event' => 'FA\\Events\\DatabasePostWriteEvent'],
];

foreach ($workflows as $workflow) {
    start_row();
    label_cell($workflow['name']);
    label_cell($workflow['event']);
    label_cell('<a href="#">Edit</a> | <a href="#">Delete</a>');
    end_row();
}

end_table();

// Form to create new workflow
display_heading(_('Create New Workflow'));

start_table(TABLESTYLE2);
text_row(_('Workflow Name:'), 'workflow_name', '', 50, 50);
text_row(_('Trigger Event:'), 'event_trigger', 'FA\\Events\\DatabasePreWriteEvent', 50, 100);

end_table(1);

submit_center('create_workflow', _('Create Workflow'));

end_form();

end_page();