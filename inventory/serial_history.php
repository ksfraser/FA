<?php
$page_security = 'SA_INVENTORY';
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/inventory/includes/inventory_db.inc");
include_once($path_to_root . "/inventory/includes/db/items_db.inc");
include_once($path_to_root . "/inventory/includes/serial_tracking_db.inc");

$js = "";
if ($SysPrefs->use_popup_windows)
    $js .= get_js_open_window(900, 500);
if (user_use_date_picker())
    $js .= get_js_date_picker();

page(_($help_context = "Serial Number History"), false, false, "", $js);

//---------------------------------------------------------------------------------------------

function display_serial_history_form()
{
    start_form();

    start_table(TABLESTYLE_NOBORDER);
    start_row();

    stock_items_list_cells(_("Item:"), 'stock_id', null, false, true, false, true);
    text_cells(_("Serial Number:"), 'serial_no', null, 20, 30);
    date_cells(_("From:"), 'from_date', '', null, -30);
    date_cells(_("To:"), 'to_date', '', null, 1);

    submit_cells('Search', _("Search"), '', _('Search'), 'default');

    end_row();
    end_table(1);

    end_form();
}

function display_serial_history($stock_id, $serial_no, $from_date, $to_date)
{
    $sql = "SELECT sm.*, m.tran_date, m.type, m.trans_no, m.reference, m.qty,
                   m.loc_code, m.loc_code2, t.type_name, i.description,
                   l1.location_name as from_location, l2.location_name as to_location
            FROM 0_stock_serial_moves sm
            LEFT JOIN 0_stock_moves m ON sm.stockmoveno = m.trans_id
            LEFT JOIN 0_sys_types t ON m.type = t.type_id
            LEFT JOIN 0_stock_master i ON sm.stock_id = i.stock_id
            LEFT JOIN 0_locations l1 ON m.loc_code = l1.loc_code
            LEFT JOIN 0_locations l2 ON m.loc_code2 = l2.loc_code
            WHERE 1=1";

    $params = array();

    if (!empty($stock_id)) {
        $sql .= " AND sm.stock_id = ?";
        $params[] = $stock_id;
    }

    if (!empty($serial_no)) {
        $sql .= " AND sm.serial_no LIKE ?";
        $params[] = '%' . $serial_no . '%';
    }

    if (!empty($from_date)) {
        $sql .= " AND m.tran_date >= ?";
        $params[] = date2sql($from_date);
    }

    if (!empty($to_date)) {
        $sql .= " AND m.tran_date <= ?";
        $params[] = date2sql($to_date);
    }

    $sql .= " ORDER BY m.tran_date DESC, m.trans_id DESC, sm.serial_no";

    $result = db_query($sql, "Could not get serial history");

    if (db_num_rows($result) == 0) {
        display_note(_("No serial number movements found matching the criteria."));
        return;
    }

    display_heading(_("Serial Number Movement History"));

    start_table(TABLESTYLE);
    $th = array(_("Date"), _("Type"), _("Reference"), _("Item"), _("Serial Number"),
                _("From Location"), _("To Location"), _("Quantity"));
    table_header($th);

    $k = 0;
    $current_serial = '';
    while ($myrow = db_fetch($result)) {
        alt_table_row_color($k);

        // Highlight when serial number changes
        if ($current_serial != $myrow["serial_no"]) {
            $current_serial = $myrow["serial_no"];
            // Could add special styling here
        }

        label_cell(sql2date($myrow["tran_date"]));
        label_cell($myrow["type_name"]);
        label_cell(get_trans_view_str($myrow["type"], $myrow["trans_no"], $myrow["reference"]));
        label_cell($myrow["stock_id"] . " - " . $myrow["description"]);
        label_cell($myrow["serial_no"]);
        label_cell($myrow["from_location"] ?? $myrow["loc_code"]);
        label_cell($myrow["to_location"] ?? ($myrow["loc_code2"] ?? ''));
        qty_cell($myrow["moveqty"]);

        end_row();
    }

    end_table(1);

    // Display summary statistics
    display_serial_history_summary($stock_id, $serial_no, $from_date, $to_date);
}

function display_serial_history_summary($stock_id, $serial_no, $from_date, $to_date)
{
    $sql = "SELECT sm.serial_no, COUNT(*) as movement_count,
                   SUM(CASE WHEN m.qty > 0 THEN m.qty ELSE 0 END) as total_in,
                   SUM(CASE WHEN m.qty < 0 THEN ABS(m.qty) ELSE 0 END) as total_out,
                   MIN(m.tran_date) as first_movement,
                   MAX(m.tran_date) as last_movement
            FROM 0_stock_serial_moves sm
            LEFT JOIN 0_stock_moves m ON sm.stockmoveno = m.trans_id
            WHERE 1=1";

    $params = array();

    if (!empty($stock_id)) {
        $sql .= " AND sm.stock_id = ?";
        $params[] = $stock_id;
    }

    if (!empty($serial_no)) {
        $sql .= " AND sm.serial_no LIKE ?";
        $params[] = '%' . $serial_no . '%';
    }

    if (!empty($from_date)) {
        $sql .= " AND m.tran_date >= ?";
        $params[] = date2sql($from_date);
    }

    if (!empty($to_date)) {
        $sql .= " AND m.tran_date <= ?";
        $params[] = date2sql($to_date);
    }

    $sql .= " GROUP BY sm.serial_no ORDER BY sm.serial_no";

    $result = db_query($sql, "Could not get serial history summary");

    if (db_num_rows($result) == 0) {
        return;
    }

    display_heading(_("Serial Number Summary"));

    start_table(TABLESTYLE);
    $th = array(_("Serial Number"), _("Movements"), _("Total In"), _("Total Out"),
                _("Net Change"), _("First Movement"), _("Last Movement"));
    table_header($th);

    $k = 0;
    while ($myrow = db_fetch($result)) {
        alt_table_row_color($k);

        $net_change = $myrow["total_in"] - $myrow["total_out"];

        label_cell($myrow["serial_no"]);
        label_cell($myrow["movement_count"]);
        qty_cell($myrow["total_in"]);
        qty_cell($myrow["total_out"]);
        qty_cell($net_change, false, '', 'class=' . ($net_change >= 0 ? 'positive' : 'negative'));
        label_cell(sql2date($myrow["first_movement"]));
        label_cell(sql2date($myrow["last_movement"]));

        end_row();
    }

    end_table(1);
}

//---------------------------------------------------------------------------------------------

$stock_id = get_post('stock_id');
$serial_no = get_post('serial_no');
$from_date = get_post('from_date');
$to_date = get_post('to_date');

display_serial_history_form();

if (isset($_POST['Search']) || !empty($stock_id) || !empty($serial_no)) {
    display_serial_history($stock_id, $serial_no, $from_date, $to_date);
}

end_page();