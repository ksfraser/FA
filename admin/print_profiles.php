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
$page_security = 'SA_PRINTPROFILE';
$path_to_root = "..";
include($path_to_root . "/includes/session.inc");
include($path_to_root . "/admin/db/printers_db.inc");
include($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/ui_strings.php");

page(_($help_context = "Printing Profiles"));

$selected_id = RequestService::getPostStatic('profile_id','');

//-------------------------------------------------------------------------------------------------
// Returns array of defined reports
//
function get_reports() {
	global $path_to_root, $SysPrefs;

	if ($SysPrefs->go_debug || !isset($_SESSION['reports'])) {	
	// to save time, store in session.
		$paths = array (
			$path_to_root.'/reporting/',
			company_path(). '/reporting/');
		$reports = array( '' => _('Default printing destination'));

		foreach($paths as $dirno => $path) {
			$repdir = opendir($path);
			while(false !== ($fname = readdir($repdir)))
			{
				// reports have filenames in form rep(repid).php 
				// where repid must contain at least one digit (reports_main.php is not ;)
				if (is_file($path.$fname) 
					&& preg_match('/rep(.*[0-9]+.*)[.]php/', $fname, $match))
				{
					$repno = $match[1];
					$title = '';

					$line = file_get_contents($path.$fname);
					if (preg_match('/.*(FrontReport\()\s*_\([\'"]([^\'"]*)/', $line, $match)) {
						$title = trim($match[2]);
					}
					else // for any 3rd party printouts without FrontReport() class use
					if (preg_match('/.*(\$Title).*[\'"](.*)[\'"].+/', $line, $match)) {
						$title = trim($match[2]);
					}
					$reports[$repno] = $title;
				}
			}
			closedir();
		}
		ksort($reports);
		$_SESSION['reports'] = $reports;
	}
	return $_SESSION['reports'];
}

function clear_form() 
{
	global $selected_id, $Ajax;

	$selected_id = '';
	$_POST['name'] = '';
	$Ajax->activate('_page_body');
}

function check_delete($name)
{
	// check if selected profile is used by any user
	if ($name=='') return 0; // cannot delete system default profile
	return key_in_foreign_table($name, 'users', 'print_profile');
}
//-------------------------------------------------------------------------------------------
if ( RequestService::getPostStatic('submit'))
{

	$error = 0;

	if ($_POST['profile_id'] == '' && empty($_POST['name']))
	{
		$error = 1;
		UiMessageService::displayError( _(UI_TEXT_PRINTING_PROFILE_NAME_CANNOT_BE_EMPTY_ERROR));
		set_focus('name');
	} 

	if (!$error)
	{
		$prof = array('' => RequestService::getPostStatic('Prn')); // store default value/profile name
		foreach (get_reports() as $rep => $descr) {
			$val = RequestService::getPostStatic('Prn'.$rep);
			$prof[$rep] = $val;
		}
		if ($_POST['profile_id']=='')
			$_POST['profile_id'] = RequestService::getPostStatic('name');
		
		update_printer_profile($_POST['profile_id'], $prof);
		if ($selected_id == '') {
			display_notification_centered(_('New printing profile has been created')); 
			clear_form($selected_id);
		} else {
			display_notification_centered(_('Printing profile has been updated'));
		}
	}
}

if(RequestService::getPostStatic('delete'))
{
 	if (!check_delete(RequestService::getPostStatic('name'))) {
		delete_printer_profile($selected_id);
		\FA\Services\UiMessageService::displayNotification(_('Selected printing profile has been deleted'));
		clear_form();
 	}
}

if(RequestService::getPostStatic('_profile_id_update')) {
	$Ajax->activate('_page_body');
}

start_form();
start_table();
print_profiles_list_row(_('Select printing profile'). ':', 'profile_id', null,
	_('New printing profile'), true);
end_table();
echo '<hr>';
start_table();
if (RequestService::getPostStatic('profile_id') == '')
	text_row(_(UI_TEXT_PRINTING_PROFILE_NAME_LABEL).':', 'name', null, 30, 30);
else
	label_cells(_(UI_TEXT_PRINTING_PROFILE_NAME_LABEL).':', RequestService::getPostStatic('profile_id'));
end_table(1);

$result = get_print_profile(RequestService::getPostStatic('profile_id'));
$prints = array();
while ($myrow = db_fetch($result)) {
	$prints[$myrow['report']] = $myrow['printer'];
}

start_table(TABLESTYLE);
$th = array(_(UI_TEXT_REPORT_ID), _(UI_TEXT_DESCRIPTION), _(UI_TEXT_PRINTER));
table_header($th);

$k = 0;
$unkn = 0;
foreach(get_reports() as $rep => $descr)
{
	alt_table_row_color($k);

    label_cell($rep=='' ? '-' : $rep, 'align=center');
    label_cell($descr == '' ? '???<sup>1)</sup>' : _($descr));
	$_POST['Prn'.$rep] = isset($prints[$rep]) ? $prints[$rep] : '';
    echo '<td>';
	echo printers_list('Prn'.$rep, null, 
		$rep == '' ? _('Browser support') : _('Default'));
	echo '</td>';
	if ($descr == '') $unkn = 1;
    end_row();
}
end_table();
if ($unkn)
	display_note('<sup>1)</sup>&nbsp;-&nbsp;'._(UI_TEXT_NO_TITLE_FOUND_IN_REPORT_DEFINITION), 0, 1, '');
else
	echo '<br>';

div_start('controls');
if (RequestService::getPostStatic('profile_id') == '') {
	submit_center('submit', _(UI_TEXT_ADD_NEW_PROFILE), true, '', 'default');
} else {
	submit_center_first('submit', _(UI_TEXT_UPDATE_PROFILE), 
	  _(UI_TEXT_UPDATE_PRINTER_PROFILE), 'default');
	submit_center_last('delete', _(UI_TEXT_DELETE_PROFILE), 
	  _(UI_TEXT_DELETE_PRINTER_PROFILE), true);
}
div_end();

end_form();
end_page();

