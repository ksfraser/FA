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
$page_security = 'SA_CHGPASSWD';
$path_to_root="..";
include_once($path_to_root . "/includes/session.inc");

page(_($help_context = "Change password"));

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/ui_strings.php");

include_once($path_to_root . "/admin/db/users_db.inc");

function can_process()
{

	$Auth_Result = hook_authenticate($_SESSION["wa_current_user"]->username, $_POST['cur_password']);

	if (!isset($Auth_Result))	// if not used external login: standard method
		$Auth_Result = get_user_auth($_SESSION["wa_current_user"]->username, md5($_POST['cur_password']));

	if (!$Auth_Result)
   	{
  		UiMessageService::displayError( _(UI_TEXT_INVALID_PASSWORD_ENTERED));
		set_focus('cur_password');
   		return false;
   	}
	
   	if (strlen($_POST['password']) < 4)
   	{
  		UiMessageService::displayError( _(UI_TEXT_PASSWORD_TOO_SHORT));
		set_focus('password');
   		return false;
   	}

   	if (strstr($_POST['password'], $_SESSION["wa_current_user"]->username) != false)
   	{
   		UiMessageService::displayError( _(UI_TEXT_PASSWORD_CANNOT_CONTAIN_LOGIN));
		set_focus('password');
   		return false;
   	}

   	if ($_POST['password'] != $_POST['passwordConfirm'])
   	{
   		UiMessageService::displayError( _(UI_TEXT_PASSWORDS_DO_NOT_MATCH));
		set_focus('password');
   		return false;
   	}

	return true;
}

	if (isset($_POST['UPDATE_ITEM']) && check_csrf_token())
	{

		if (can_process())
		{
			if ($SysPrefs->allow_demo_mode) {
			    \FA\Services\UiMessageService::displayWarning(_(UI_TEXT_PASSWORD_CHANGE_DEMO_MODE));
			} else {
				update_user_password($_SESSION["wa_current_user"]->user, 
					$_SESSION["wa_current_user"]->username,
					md5($_POST['password']));
			    \FA\Services\UiMessageService::displayNotification(_(UI_TEXT_PASSWORD_UPDATED));
			}
			$Ajax->activate('_page_body');
		}
	}start_form();

start_table(TABLESTYLE);

$myrow = get_user($_SESSION["wa_current_user"]->user);

label_row(_(UI_TEXT_USER_LOGIN_LABEL), $myrow['user_id']);

$_POST['cur_password'] = "";
$_POST['password'] = "";
$_POST['passwordConfirm'] = "";

password_row(_(UI_TEXT_CURRENT_PASSWORD_LABEL), 'cur_password', $_POST['cur_password']);
password_row(_(UI_TEXT_NEW_PASSWORD_LABEL), 'password', $_POST['password']);
password_row(_(UI_TEXT_REPEAT_NEW_PASSWORD_LABEL), 'passwordConfirm', $_POST['passwordConfirm']);

table_section_title(_(UI_TEXT_ENTER_NEW_PASSWORD_INSTRUCTION));

end_table(1);

submit_center( 'UPDATE_ITEM', _('Change password'), true, '',  'default');
end_form();
end_page();
