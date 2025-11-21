<?php
/**********************************************************************
    Copyright (C) FrontAccounting, LLC.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
$page_security = 'SA_GLACCOUNT';
$path_to_root = "../..";
include($path_to_root . "/includes/session.inc");

$js = "";
if ($SysPrefs->use_popup_windows && $SysPrefs->use_popup_search)
	$js .= get_js_open_window(900, 500);

page(_($help_context = UI_TEXT_CHART_OF_ACCOUNTS_TITLE), false, false, "", $js);

include($path_to_root . "/includes/ui.inc");
include($path_to_root . "/gl/includes/gl_db.inc");
include_once($path_to_root . "/admin/db/tags_db.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/includes/ui_strings.php");

check_db_has_gl_account_groups(_(UI_TEXT_NO_ACCOUNT_GROUPS_DEFINED));

if (isset($_GET["id"]))
	$_POST["id"] = $_GET["id"];	

//-------------------------------------------------------------------------------------

if (isset($_POST['_AccountList_update'])) 
{
	$_POST['selected_account'] = $_POST['AccountList'];
	unset($_POST['account_code']);
}

if (isset($_POST['selected_account']))
{
	$selected_account = $_POST['selected_account'];
} 
elseif (isset($_GET['selected_account']))
{
	$selected_account = $_GET['selected_account'];
}
else
	$selected_account = "";
//-------------------------------------------------------------------------------------

if (isset($_POST['add']) || isset($_POST['update'])) 
{

	$input_error = 0;

	if (strlen(trim($_POST['account_code'])) == 0) 
	{
		$input_error = 1;
		UiMessageService::displayError( _(UI_TEXT_ACCOUNT_CODE_MUST_BE_ENTERED));
		set_focus('account_code');
	} 
	elseif (strlen(trim($_POST['account_name'])) == 0) 
	{
		$input_error = 1;
		UiMessageService::displayError( _(UI_TEXT_ACCOUNT_NAME_CANNOT_BE_EMPTY));
		set_focus('account_name');
	} 
	elseif (!$SysPrefs->accounts_alpha() && !preg_match("/^[0-9.]+$/",$_POST['account_code'])) // we only allow 0-9 and a dot
	{
	    $input_error = 1;
	    UiMessageService::displayError( _(UI_TEXT_ACCOUNT_CODE_MUST_BE_NUMERIC));
		set_focus('account_code');
	}
	if ($input_error != 1)
	{
		if ($SysPrefs->accounts_alpha() == 2)
			$_POST['account_code'] = strtoupper($_POST['account_code']);

		if (!isset($_POST['account_tags']))
			$_POST['account_tags'] = array();

    	if ($selected_account) 
		{
			if (RequestService::getPostStatic('inactive') == 1 && is_bank_account($_POST['account_code']))
			{
				UiMessageService::displayError(_(UI_TEXT_ACCOUNT_BELONGS_TO_BANK_CANNOT_INACTIVATE));
			}
    		elseif (update_gl_account($_POST['account_code'], $_POST['account_name'], 
				$_POST['account_type'], $_POST['account_code2'])) {
				update_record_status($_POST['account_code'], $_POST['inactive'],
					'chart_master', 'account_code');
				update_tag_associations(TAG_ACCOUNT, $_POST['account_code'], 
					$_POST['account_tags']);
				$Ajax->activate('account_code'); // in case of status change
				display_notification(_(UI_TEXT_ACCOUNT_DATA_UPDATED));
			}
		}
    	else 
		{
    		if (add_gl_account($_POST['account_code'], $_POST['account_name'], 
				$_POST['account_type'], $_POST['account_code2']))
				{
					add_tag_associations($_POST['account_code'], $_POST['account_tags']);
					display_notification(_(UI_TEXT_NEW_ACCOUNT_ADDED));
					$selected_account = $_POST['AccountList'] = $_POST['account_code'];
				}
			else
                 UiMessageService::displayError(_(UI_TEXT_ACCOUNT_NOT_ADDED_DUPLICATE_CODE));
		}
		$Ajax->activate('_page_body');
	}
} 

//-------------------------------------------------------------------------------------

function can_delete($selected_account)
{
	if ($selected_account == "")
		return false;

	if (key_in_foreign_table($selected_account, 'gl_trans', 'account'))
	{
		UiMessageService::displayError(_(UI_TEXT_CANNOT_DELETE_ACCOUNT_TRANSACTIONS));
		return false;
	}

	if (gl_account_in_company_defaults($selected_account))
	{
		UiMessageService::displayError(_(UI_TEXT_CANNOT_DELETE_ACCOUNT_COMPANY_DEFAULTS));
		return false;
	}

	if (key_in_foreign_table($selected_account, 'bank_accounts', 'account_code'))
	{
		UiMessageService::displayError(_(UI_TEXT_CANNOT_DELETE_ACCOUNT_BANK_ACCOUNT));
		return false;
	}

	if (gl_account_in_stock_category($selected_account))
	{
		UiMessageService::displayError(_(UI_TEXT_CANNOT_DELETE_ACCOUNT_ITEM_CATEGORIES));
		return false;
	}

	if (gl_account_in_stock_master($selected_account))
	{
		UiMessageService::displayError(_(UI_TEXT_CANNOT_DELETE_ACCOUNT_ITEMS));
		return false;
	}

	if (gl_account_in_tax_types($selected_account))
	{
		UiMessageService::displayError(_(UI_TEXT_CANNOT_DELETE_ACCOUNT_TAXES));
		return false;
	}

	if (gl_account_in_cust_branch($selected_account))
	{
		UiMessageService::displayError(_(UI_TEXT_CANNOT_DELETE_ACCOUNT_CUSTOMER_BRANCHES));
		return false;
	}
	if (gl_account_in_suppliers($selected_account))
	{
		UiMessageService::displayError(_(UI_TEXT_CANNOT_DELETE_ACCOUNT_SUPPLIERS));
		return false;
	}

	if (gl_account_in_quick_entry_lines($selected_account))
	{
		UiMessageService::displayError(_(UI_TEXT_CANNOT_DELETE_ACCOUNT_QUICK_ENTRY_LINES));
		return false;
	}

	return true;
}

//--------------------------------------------------------------------------------------

if (isset($_POST['delete'])) 
{

	if (can_delete($selected_account))
	{
		delete_gl_account($selected_account);
		$selected_account = $_POST['AccountList'] = '';
		delete_tag_associations(TAG_ACCOUNT,$selected_account, true);
		$selected_account = $_POST['AccountList'] = '';
		display_notification(_(UI_TEXT_SELECTED_ACCOUNT_DELETED));
		unset($_POST['account_code']);
		$Ajax->activate('_page_body');
	}
} 

//-------------------------------------------------------------------------------------
$filter_id = (isset($_POST["id"]));

start_form();

if (db_has_gl_accounts()) 
{
	start_table(TABLESTYLE_NOBORDER);
	start_row();
	if ($filter_id)
		gl_all_accounts_list_cells(null, 'AccountList', null, false, false, _(UI_TEXT_NEW_ACCOUNT), true, RequestService::checkValueStatic('show_inactive'), $_POST['id']);
	else
		gl_all_accounts_list_cells(null, 'AccountList', null, false, false, _(UI_TEXT_NEW_ACCOUNT), true, RequestService::checkValueStatic('show_inactive'));
	check_cells(_(UI_TEXT_SHOW_INACTIVE_LABEL), 'show_inactive', null, true);
	end_row();
	end_table();
	if (RequestService::getPostStatic('_show_inactive_update')) {
		$Ajax->activate('AccountList');
		set_focus('AccountList');
	}
}
	
br(1);
start_table(TABLESTYLE2);

if ($selected_account != "") 
{
	//editing an existing account
	$myrow = get_gl_account($selected_account);

	$_POST['account_code'] = $myrow["account_code"];
	$_POST['account_code2'] = $myrow["account_code2"];
	$_POST['account_name']	= $myrow["account_name"];
	$_POST['account_type'] = $myrow["account_type"];
 	$_POST['inactive'] = $myrow["inactive"];
 	
 	$tags_result = get_tags_associated_with_record(TAG_ACCOUNT, $selected_account);
 	$tagids = array();
 	while ($tag = db_fetch($tags_result)) 
 	 	$tagids[] = $tag['id'];
 	$_POST['account_tags'] = $tagids;

	hidden('account_code', $_POST['account_code']);
	hidden('selected_account', $selected_account);
		
	label_row(_(UI_TEXT_ACCOUNT_CODE_LABEL), $_POST['account_code']);
} 
else
{
	if (!isset($_POST['account_code'])) {
		$_POST['account_tags'] = array();
		$_POST['account_code'] = $_POST['account_code2'] = '';
		$_POST['account_name']	= $_POST['account_type'] = '';
 		$_POST['inactive'] = 0;
		if ($filter_id) $_POST['account_type'] = $_POST['id'];
	}
	text_row_ex(_(UI_TEXT_ACCOUNT_CODE_LABEL), 'account_code', 15);
}

text_row_ex(_(UI_TEXT_ACCOUNT_CODE_2_LABEL), 'account_code2', 15);

text_row_ex(_(UI_TEXT_ACCOUNT_NAME_LABEL), 'account_name', 60);

gl_account_types_list_row(_(UI_TEXT_ACCOUNT_GROUP_LABEL), 'account_type', null);

tag_list_row(_(UI_TEXT_ACCOUNT_TAGS_LABEL), 'account_tags', 5, TAG_ACCOUNT, true);

record_status_list_row(_(UI_TEXT_ACCOUNT_STATUS_LABEL), 'inactive');
end_table(1);

if ($selected_account == "") 
{
	submit_center('add', _(UI_TEXT_ADD_ACCOUNT_BUTTON), true, '', 'default');
} 
else 
{
    submit_center_first('update', _(UI_TEXT_UPDATE_ACCOUNT_BUTTON), '', 'default');
    submit_center_last('delete', _(UI_TEXT_DELETE_ACCOUNT_BUTTON), '',true);
}
end_form();

end_page();

