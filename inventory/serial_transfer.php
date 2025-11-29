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

page(_($help_context = "Serial Number Transfer"), false, false, "", $js);

//---------------------------------------------------------------------------------------------

function can_process()
{
    if (!is_serialised_item($_POST['stock_id'])) {
        display_error(_("The selected item is not serialised."));
        return false;
    }

    if (empty($_POST['from_loc'])) {
        display_error(_("From location must be selected."));
        return false;
    }

    if (empty($_POST['to_loc'])) {
        display_error(_("To location must be selected."));
        return false;
    }

    if ($_POST['from_loc'] == $_POST['to_loc']) {
        display_error(_("From and To locations cannot be the same."));
        return false;
    }

    if (empty($_POST['serial_nos'])) {
        display_error(_("At least one serial number must be selected."));
        return false;
    }

    return true;
}

function handle_transfer()
{
    if (!can_process()) {
        return;
    }

    $stock_id = $_POST['stock_id'];
    $from_loc = $_POST['from_loc'];
    $to_loc = $_POST['to_loc'];
    $serial_nos = $_POST['serial_nos'];
    $reference = $_POST['reference'] ?: 'Serial Transfer';

    begin_transaction();

    $success_count = 0;
    $errors = array();

    foreach ($serial_nos as $serial_no) {
        // Create stock movement record
        $stock_move_no = add_stock_move(ST_LOCTRANSFER, $stock_id, 0, $from_loc, $to_loc,
            date2sql(Today()), $reference, 1, 0, 0, 0, 0, 0, '', 0, 0, 0);

        if ($stock_move_no) {
            // Move the serial item
            if (move_serial_item($stock_id, $from_loc, $to_loc, $serial_no, 1)) {
                // Add serial movement record
                add_serial_movement($stock_move_no, $stock_id, $serial_no, 1);
                $success_count++;
            } else {
                $errors[] = sprintf(_("Failed to move serial number %s"), $serial_no);
            }
        } else {
            $errors[] = sprintf(_("Failed to create stock movement for serial %s"), $serial_no);
        }
    }

    if ($success_count > 0) {
        commit_transaction();
        display_notification(sprintf(_("%d serial number(s) successfully transferred."), $success_count));

        if (!empty($errors)) {
            display_error(_("Some transfers failed:"));
            foreach ($errors as $error) {
                display_error($error);
            }
        }

        // Clear form
        $_POST = array();
    } else {
        rollback_transaction();
        display_error(_("Transfer failed. No serial numbers were transferred."));
        foreach ($errors as $error) {
            display_error($error);
        }
    }
}

function display_transfer_form()
{
    start_form();

    start_table(TABLESTYLE2);
    start_row();
    label_cell(_("Transfer Serial Numbers"), "class='tableheader2'", 2);
    end_row();

    stock_items_list_row(_("Item:"), 'stock_id', null, false, true, false, true);

    locations_list_row(_("From Location:"), 'from_loc', null);
    locations_list_row(_("To Location:"), 'to_loc', null);

    text_row(_("Reference:"), 'reference', null, 30, 50);

    end_table(1);

    // Serial number selection
    if (!empty($_POST['stock_id']) && !empty($_POST['from_loc'])) {
        display_serial_selection($_POST['stock_id'], $_POST['from_loc']);
    }

    submit_center('transfer', _("Transfer Serial Numbers"), true, '', 'default');

    end_form();
}

function display_serial_selection($stock_id, $loc_code)
{
    $result = get_available_serial_items($stock_id, $loc_code);

    if (db_num_rows($result) == 0) {
        display_note(_("No serial numbers available for transfer at the selected location."));
        return;
    }

    display_heading(_("Select Serial Numbers to Transfer"));

    start_table(TABLESTYLE);
    $th = array(_("Select"), _("Serial Number"), _("Quantity"), _("Expiration Date"), _("Quality Notes"));
    table_header($th);

    $k = 0;
    while ($myrow = db_fetch($result)) {
        alt_table_row_color($k);

        check_cells('', 'serial_nos[]', $myrow["serial_no"], false, '', "value='" . $myrow["serial_no"] . "'");
        label_cell($myrow["serial_no"]);
        qty_cell($myrow["quantity"]);
        label_cell(sql2date($myrow["expiration_date"]));
        label_cell($myrow["quality_text"]);

        end_row();
    }

    end_table(1);
}

//---------------------------------------------------------------------------------------------

if (isset($_POST['transfer'])) {
    handle_transfer();
}

display_transfer_form();

end_page();