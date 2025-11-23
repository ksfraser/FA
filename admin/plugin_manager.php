<?php
$page_security = 'SA_PLUGIN_MANAGEMENT';
$path_to_root = "../..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/Plugins/PluginManager.php");

$page_title = _("Plugin Manager");

if (isset($_GET['action']) && isset($_GET['plugin'])) {
    $action = $_GET['action'];
    $pluginName = $_GET['plugin'];
    $pluginManager = \FA\Plugins\PluginManager::getInstance();

    switch ($action) {
        case 'install':
            if ($pluginManager->installPlugin($pluginName)) {
                display_notification(_("Plugin installed successfully"));
            } else {
                display_error(_("Failed to install plugin"));
            }
            break;

        case 'activate':
            if ($pluginManager->activatePlugin($pluginName)) {
                display_notification(_("Plugin activated successfully"));
            } else {
                display_error(_("Failed to activate plugin"));
            }
            break;

        case 'deactivate':
            if ($pluginManager->deactivatePlugin($pluginName)) {
                display_notification(_("Plugin deactivated successfully"));
            } else {
                display_error(_("Failed to deactivate plugin"));
            }
            break;

        case 'uninstall':
            if ($pluginManager->uninstallPlugin($pluginName)) {
                display_notification(_("Plugin uninstalled successfully"));
            } else {
                display_error(_("Failed to uninstall plugin"));
            }
            break;
    }

    meta_forward($_SERVER['PHP_SELF']);
}

include_once($path_to_root . "/includes/ui.inc");

page($page_title);

// Load plugins from plugins directory
$pluginManager = \FA\Plugins\PluginManager::getInstance();
$pluginManager->loadPluginsFromDirectory($path_to_root . "/plugins");

// Display plugin management interface
start_form();

start_table(TABLESTYLE);
$tableheader = array(
    _("Plugin Name"),
    _("Version"),
    _("Description"),
    _("Author"),
    _("Status"),
    _("Actions")
);
table_header($tableheader);

$registry = $pluginManager->getPluginRegistry();

foreach ($registry as $pluginData) {
    start_row();

    label_cell($pluginData['name']);
    label_cell($pluginData['version']);
    label_cell($pluginData['description'] ?? '');
    label_cell($pluginData['author'] ?? '');

    // Status
    $status = '';
    if ($pluginData['active']) {
        $status = _("Active");
        $status_class = "class='success'";
    } elseif ($pluginData['installed']) {
        $status = _("Installed");
        $status_class = "class='info'";
    } else {
        $status = _("Available");
        $status_class = "class='warning'";
    }
    label_cell("<span {$status_class}>{$status}</span>");

    // Actions
    $actions = array();

    if (!$pluginData['installed']) {
        $actions[] = "<a href='" . $_SERVER['PHP_SELF'] . "?action=install&plugin=" . urlencode($pluginData['name']) . "'>" . _("Install") . "</a>";
    } elseif (!$pluginData['active']) {
        $actions[] = "<a href='" . $_SERVER['PHP_SELF'] . "?action=activate&plugin=" . urlencode($pluginData['name']) . "'>" . _("Activate") . "</a>";
        $actions[] = "<a href='" . $_SERVER['PHP_SELF'] . "?action=uninstall&plugin=" . urlencode($pluginData['name']) . "' onclick='return confirm(\"" . _("Are you sure you want to uninstall this plugin?") . "\")'>" . _("Uninstall") . "</a>";
    } else {
        $actions[] = "<a href='" . $_SERVER['PHP_SELF'] . "?action=deactivate&plugin=" . urlencode($pluginData['name']) . "'>" . _("Deactivate") . "</a>";
    }

    label_cell(implode(' | ', $actions));

    end_row();
}

end_table(1);

end_form();

end_page();