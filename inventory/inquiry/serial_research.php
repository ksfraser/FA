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

page(_($help_context = "Serial Number Research"), false, false, "", $js);

//--------------------------------------------------------------------------------------------

function display_serial_research_results($serial_no)
{
    $sql = "SELECT s.stock_id, s.loc_code, s.quantity, s.expiration_date, s.quality_text, s.createdate,
                   i.description, l.location_name
            FROM 0_stock_serial_items s
            LEFT JOIN 0_stock_master i ON s.stock_id = i.stock_id
            LEFT JOIN 0_locations l ON s.loc_code = l.loc_code
            WHERE s.serial_no = " . db_escape($serial_no) . "
            ORDER BY s.createdate DESC";

    $result = db_query($sql, "Could not get serial research results");

    if (db_num_rows($result) == 0) {
        display_note(_("No serial number found matching the search criteria."));
        return;
    }

    start_table(TABLESTYLE);
    $th = array(_("Stock ID"), _("Description"), _("Location"), _("Quantity"), _("Expiration Date"), _("Quality Text"), _("Create Date"));
    table_header($th);

    $k = 0;
    while ($myrow = db_fetch($result)) {
        alt_table_row_color($k);

        label_cell($myrow["stock_id"]);
        label_cell($myrow["description"]);
        label_cell($myrow["location_name"]);
        qty_cell($myrow["quantity"]);
        label_cell(sql2date($myrow["expiration_date"]));
        label_cell($myrow["quality_text"]);
        label_cell(sql2date($myrow["createdate"]));

        end_row();
    }

    end_table(1);

    // Display movement history
    display_serial_movement_history($serial_no);
}

//--------------------------------------------------------------------------------------------

function display_serial_movement_history($serial_no)
{
    $sql = "SELECT m.moveqty, m.stockmoveno, sm.type, sm.trans_no, sm.tran_date, sm.reference,
                   i.description, l.location_name
            FROM 0_stock_serial_moves m
            LEFT JOIN 0_stock_moves sm ON m.stockmoveno = sm.trans_id
            LEFT JOIN 0_stock_master i ON m.stock_id = i.stock_id
            LEFT JOIN 0_locations l ON sm.loc_code = l.loc_code
            WHERE m.serial_no = " . db_escape($serial_no) . "
            ORDER BY sm.tran_date DESC, m.stkitmmoveno DESC";

    $result = db_query($sql, "Could not get serial movement history");

    if (db_num_rows($result) == 0) {
        display_note(_("No movement history found for this serial number."));
        return;
    }

    display_heading(_("Movement History"));

    start_table(TABLESTYLE);
    $th = array(_("Date"), _("Type"), _("Reference"), _("Stock ID"), _("Description"), _("Location"), _("Quantity"));
    table_header($th);

    $k = 0;
    while ($myrow = db_fetch($result)) {
        alt_table_row_color($k);

        label_cell(sql2date($myrow["tran_date"]));
        label_cell(systypes::get_name($myrow["type"]));
        label_cell(get_trans_view_str($myrow["type"], $myrow["trans_no"]));
        label_cell($myrow["stock_id"]);
        label_cell($myrow["description"]);
        label_cell($myrow["location_name"]);
        qty_cell($myrow["moveqty"]);

        end_row();
    }

    end_table(1);
}

//--------------------------------------------------------------------------------------------

start_form();

start_table(TABLESTYLE_NOBORDER);
start_row();

text_cells(_("Serial Number:"), 'serial_no', get_post('serial_no'), 30, 30);

end_row();
end_table();

submit_center('search', _("Search"));

if (isset($_POST['search']) && get_post('serial_no')) {
    display_serial_research_results(get_post('serial_no'));
}

end_form();

end_page();