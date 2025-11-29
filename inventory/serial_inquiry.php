<?php
$page_security = 'SA_INVENTORY';
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/inventory/includes/inventory_db.inc");
include_once($path_to_root . "/inventory/includes/db/items_db.inc");

// Include serial tracking functions
include_once($path_to_root . "/inventory/includes/serial_tracking_db.inc");

$js = "";
if ($SysPrefs->use_popup_windows)
    $js .= get_js_open_window(900, 500);
if (user_use_date_picker())
    $js .= get_js_date_picker();

page(_($help_context = "Serial Number Inquiry"), false, false, "", $js);

if (isset($_GET['serial_no'])) {
    $serial_no = $_GET['serial_no'];
} elseif (isset($_POST['serial_no'])) {
    $serial_no = $_POST['serial_no'];
} else {
    $serial_no = '';
}

if (isset($_GET['stock_id'])) {
    $stock_id = $_GET['stock_id'];
} elseif (isset($_POST['stock_id'])) {
    $stock_id = $_POST['stock_id'];
} else {
    $stock_id = '';
}

//---------------------------------------------------------------------------------------------

function display_serial_inquiry()
{
    global $serial_no, $stock_id;

    start_form();

    start_table(TABLESTYLE_NOBORDER);
    start_row();

    stock_items_list_cells(_("Item:"), 'stock_id', $stock_id, false, true, false, true);
    text_cells(_("Serial Number:"), 'serial_no', $serial_no, 20, 30);

    submit_cells('Search', _("Search"), '', _('Search'), 'default');

    end_row();
    end_table(1);

    if (!empty($serial_no) || !empty($stock_id)) {
        display_serial_details($serial_no, $stock_id);
        display_serial_movements($serial_no, $stock_id);
    }

    end_form();
}

function display_serial_details($serial_no, $stock_id)
{
    $sql = "SELECT s.*, i.description, i.units, l.location_name
            FROM 0_stock_serial_items s
            LEFT JOIN 0_stock_master i ON s.stock_id = i.stock_id
            LEFT JOIN 0_locations l ON s.loc_code = l.loc_code
            WHERE 1=1";

    $params = array();
    if (!empty($serial_no)) {
        $sql .= " AND s.serial_no LIKE ?";
        $params[] = '%' . $serial_no . '%';
    }
    if (!empty($stock_id)) {
        $sql .= " AND s.stock_id = ?";
        $params[] = $stock_id;
    }

    $result = db_query($sql, "Could not get serial items");

    if (db_num_rows($result) == 0) {
        display_note(_("No serial numbers found matching the criteria."));
        return;
    }

    start_table(TABLESTYLE);
    $th = array(_("Item Code"), _("Description"), _("Serial Number"), _("Location"),
                _("Quantity"), _("Expiration Date"), _("Quality Notes"), _("Create Date"));
    table_header($th);

    $k = 0;
    while ($myrow = db_fetch($result)) {
        alt_table_row_color($k);

        label_cell($myrow["stock_id"]);
        label_cell($myrow["description"]);
        label_cell($myrow["serial_no"]);
        label_cell($myrow["location_name"]);
        qty_cell($myrow["quantity"]);
        label_cell(sql2date($myrow["expiration_date"]));
        label_cell($myrow["quality_text"]);
        label_cell(sql2date($myrow["createdate"]));

        end_row();
    }

    end_table(1);
}

function display_serial_movements($serial_no, $stock_id)
{
    $sql = "SELECT m.*, t.type_name, s.reference, DATE_FORMAT(m.tran_date, '%Y-%m-%d') as tran_date_fmt
            FROM 0_stock_serial_moves sm
            LEFT JOIN 0_stock_moves m ON sm.stockmoveno = m.trans_id
            LEFT JOIN 0_sys_types t ON m.type = t.type_id
            LEFT JOIN 0_refs s ON m.reference = s.reference
            WHERE 1=1";

    $params = array();
    if (!empty($serial_no)) {
        $sql .= " AND sm.serial_no LIKE ?";
        $params[] = '%' . $serial_no . '%';
    }
    if (!empty($stock_id)) {
        $sql .= " AND sm.stock_id = ?";
        $params[] = $stock_id;
    }

    $sql .= " ORDER BY m.tran_date DESC, m.trans_id DESC";

    $result = db_query($sql, "Could not get serial movements");

    if (db_num_rows($result) == 0) {
        display_note(_("No movements found for the selected serial numbers."));
        return;
    }

    display_heading(_("Serial Number Movement History"));

    start_table(TABLESTYLE);
    $th = array(_("Date"), _("Type"), _("Reference"), _("Location From"), _("Location To"),
                _("Quantity"), _("Serial Number"));
    table_header($th);

    $k = 0;
    while ($myrow = db_fetch($result)) {
        alt_table_row_color($k);

        label_cell(sql2date($myrow["tran_date"]));
        label_cell($myrow["type_name"]);
        label_cell(get_trans_view_str($myrow["type"], $myrow["trans_no"], $myrow["reference"]));
        label_cell($myrow["loc_code"]);
        label_cell($myrow["loc_code2"] ?? '');
        qty_cell($myrow["qty"]);
        label_cell($myrow["serial_no"]);

        end_row();
    }

    end_table(1);
}

//---------------------------------------------------------------------------------------------

display_serial_inquiry();

end_page();