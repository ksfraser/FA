<?php
$page_security = 'SA_SERIALITEMS';
$path_to_root = "../..";

include($path_to_root . "/includes/session.inc");
include($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/inventory/includes/inventory_db.inc");

$js = "";
if ($SysPrefs->use_popup_windows)
    $js .= get_js_open_window(900, 500);
if (user_use_date_picker())
    $js .= get_js_date_picker();

page(_($help_context = "Serial Number Management"), false, false, "", $js);

//--------------------------------------------------------------------------------------------

function display_serial_items()
{
    global $path_to_root;

    $stock_id = get_post('stock_id', '');
    $loc_code = get_post('loc_code', '');

    if ($stock_id == '' || $loc_code == '') {
        display_note(_("Please select a stock item and location first."));
        return;
    }

    $sql = "SELECT s.serial_no, s.quantity, s.expiration_date, s.quality_text, s.createdate
            FROM 0_stock_serial_items s
            WHERE s.stock_id = " . db_escape($stock_id) . "
            AND s.loc_code = " . db_escape($loc_code) . "
            ORDER BY s.serial_no";

    $result = db_query($sql, "Could not get serial items");

    start_table(TABLESTYLE);
    $th = array(_("Serial Number"), _("Quantity"), _("Expiration Date"), _("Quality Text"), _("Create Date"), "", "");
    table_header($th);

    $k = 0;
    while ($myrow = db_fetch($result)) {
        alt_table_row_color($k);

        label_cell($myrow["serial_no"]);
        qty_cell($myrow["quantity"]);
        label_cell(sql2date($myrow["expiration_date"]));
        label_cell($myrow["quality_text"]);
        label_cell(sql2date($myrow["createdate"]));

        edit_button_cell("Edit" . $myrow["serial_no"], _("Edit"));
        delete_button_cell("Delete" . $myrow["serial_no"], _("Delete"));

        end_row();
    }

    end_table(1);
}

//--------------------------------------------------------------------------------------------

function edit_serial_item($serial_no = null)
{
    global $path_to_root;

    start_form();

    if ($serial_no) {
        $sql = "SELECT * FROM 0_stock_serial_items
                WHERE stock_id = " . db_escape(get_post('stock_id')) . "
                AND loc_code = " . db_escape(get_post('loc_code')) . "
                AND serial_no = " . db_escape($serial_no);
        $result = db_query($sql, "Could not get serial item");
        $myrow = db_fetch($result);
    }

    start_table(TABLESTYLE2);

    if (!$serial_no) {
        text_row(_("Serial Number:"), 'serial_no', $myrow['serial_no'] ?? '', 30, 30);
    } else {
        label_row(_("Serial Number:"), $serial_no);
        hidden('serial_no', $serial_no);
    }

    text_row(_("Quantity:"), 'quantity', $myrow['quantity'] ?? 1, 10, 10);
    date_row(_("Expiration Date:"), 'expiration_date', $myrow['expiration_date'] ?? '', false, 0, 0, 0, null, true);
    textarea_row(_("Quality Text:"), 'quality_text', $myrow['quality_text'] ?? '', 35, 5);

    end_table(1);

    submit_center_first('save_serial', _("Save"), '', 'default');
    submit_center_last('cancel', _("Cancel"));

    end_form();
}

//--------------------------------------------------------------------------------------------

function save_serial_item()
{
    $stock_id = get_post('stock_id');
    $loc_code = get_post('loc_code');
    $serial_no = get_post('serial_no');
    $quantity = input_num('quantity');
    $expiration_date = get_post('expiration_date');
    $quality_text = get_post('quality_text');

    if ($expiration_date == '')
        $expiration_date = '0000-00-00';

    $sql = "INSERT INTO 0_stock_serial_items (stock_id, loc_code, serial_no, quantity, expiration_date, quality_text)
            VALUES (" . db_escape($stock_id) . ", " . db_escape($loc_code) . ", " . db_escape($serial_no) . ",
                   " . db_escape($quantity) . ", " . db_escape($expiration_date) . ", " . db_escape($quality_text) . ")
            ON DUPLICATE KEY UPDATE
            quantity = VALUES(quantity),
            expiration_date = VALUES(expiration_date),
            quality_text = VALUES(quality_text)";

    db_query($sql, "Could not save serial item");

    display_notification(_("Serial item saved."));
}

//--------------------------------------------------------------------------------------------

function delete_serial_item($serial_no)
{
    $stock_id = get_post('stock_id');
    $loc_code = get_post('loc_code');

    $sql = "DELETE FROM 0_stock_serial_items
            WHERE stock_id = " . db_escape($stock_id) . "
            AND loc_code = " . db_escape($loc_code) . "
            AND serial_no = " . db_escape($serial_no);

    db_query($sql, "Could not delete serial item");

    display_notification(_("Serial item deleted."));
}

//--------------------------------------------------------------------------------------------

if (isset($_GET['stock_id']) && isset($_GET['loc_code'])) {
    $_POST['stock_id'] = $_GET['stock_id'];
    $_POST['loc_code'] = $_GET['loc_code'];
}

$stock_id = get_post('stock_id', '');
$loc_code = get_post('loc_code', '');

//--------------------------------------------------------------------------------------------

if (isset($_POST['save_serial'])) {
    save_serial_item();
    $Ajax->activate('_page_body');
} elseif (isset($_POST['cancel'])) {
    unset($_POST['serial_no']);
    $Ajax->activate('_page_body');
} elseif (isset($_POST['delete'])) {
    delete_serial_item($_POST['serial_no']);
    $Ajax->activate('_page_body');
}

//--------------------------------------------------------------------------------------------

start_form();

start_table(TABLESTYLE_NOBORDER);
start_row();

stock_items_list_cells(_("Item:"), 'stock_id', $stock_id, false, true, false, true);
locations_list_cells(_("Location:"), 'loc_code', $loc_code, false, true);

end_row();
end_table();

if ($stock_id && $loc_code) {
    // Check if item is serialised
    $sql = "SELECT serialised, controlled FROM 0_stock_master WHERE stock_id = " . db_escape($stock_id);
    $result = db_query($sql, "Could not get item info");
    $item = db_fetch($result);

    if ($item['serialised'] || $item['controlled']) {
        display_serial_items();

        if (isset($_POST['Edit']) || get_post('add_new')) {
            edit_serial_item(isset($_POST['Edit']) ? key($_POST['Edit']) : null);
        } else {
            hyperlink_params($_SERVER['PHP_SELF'], _("Add new serial item"), "stock_id=$stock_id&loc_code=$loc_code&add_new=1");
        }
    } else {
        display_note(_("This item is not marked as serialised or controlled. Please edit the item to enable serial tracking."));
    }
}

end_form();

end_page();