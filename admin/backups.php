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
$page_security = 'SA_BACKUP';

$path_to_root="..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/admin/db/maintenance_db.inc");
include_once($path_to_root . "/includes/ui_strings.php");

if (RequestService::getPostStatic('view')) {
	if (!RequestService::getPostStatic('backups')) {
		UiMessageService::displayError(_('Select backup file first.'));
	} else {
		$filename = $SysPrefs->backup_dir() . clean_file_name(RequestService::getPostStatic('backups'));
		if (in_ajax()) 
			$Ajax->popup( $filename );
		else {
			header('Content-type: text/plain');
			header('Content-Length: '.filesize($filename));
			header("Content-Disposition: inline; filename=".basename($filename));
			if (substr($filename, -3, 3) == '.gz')
				header("Content-Encoding: gzip");

			if (substr($filename, -4, 4) == '.zip')
				echo db_unzip('', $filename);
			else
				readfile($filename);
			exit();
		}
	}
};

if (RequestService::getPostStatic('download')) {
	if (RequestService::getPostStatic('backups')) {
		download_file($SysPrefs->backup_dir().clean_file_name(RequestService::getPostStatic('backups')));
		exit;
	} else
		UiMessageService::displayError(_(UI_TEXT_SELECT_BACKUP_FILE_FIRST));
}

page(_($help_context = "Backup and Restore Database"), false, false, '', '');

check_paths();

function check_paths()
{
  global $SysPrefs;

	if (!file_exists($SysPrefs->backup_dir())) {
		display_error (_(UI_TEXT_BACKUP_PATHS_NOT_SET_CORRECTLY) 
			._(UI_TEXT_PLEASE_CONTACT_SYSTEM_ADMINISTRATOR)."<br>" 
			. _(UI_TEXT_CANNOT_FIND_BACKUP_DIRECTORY) . " - " . $SysPrefs->backup_dir() . "<br>");
		end_page();
		exit;
	}
}

function generate_backup($conn, $ext='no', $comm='')
{
	global $SysPrefs;

	$filename = db_backup($conn, $ext, $comm, $SysPrefs->backup_dir());
	if ($filename)
		\FA\Services\UiMessageService::displayNotification(_(UI_TEXT_BACKUP_SUCCESSFULLY_GENERATED). ' '
			. _(UI_TEXT_FILENAME) . ": " . $filename);
	else
		UiMessageService::displayError(_(UI_TEXT_DATABASE_BACKUP_FAILED));

	return $filename;
}


function get_backup_file_combo()
{
	global $path_to_root, $Ajax, $SysPrefs;
	
	$ar_files = array();
    default_focus('backups');
    $dh = opendir($SysPrefs->backup_dir());
	while (($file = readdir($dh)) !== false)
		$ar_files[] = $file;
	closedir($dh);

    rsort($ar_files);
	$opt_files = "";
    foreach ($ar_files as $file)
		if (preg_match("/.sql(.zip|.gz)?$/", $file))
    		$opt_files .= "<option value='$file'>$file</option>";

	$selector = "<select name='backups' size=2 style='height:160px;min-width:230px'>$opt_files</select>";

	$Ajax->addUpdate('backups', "_backups_sel", $selector);
	$selector = "<span id='_backups_sel'>".$selector."</span>\n";

	return $selector;
}

function compress_list_row($label, $name, $value=null)
{
	$ar_comps = array('no'=>_("No"));

    if (function_exists("gzcompress"))
    	$ar_comps['zip'] = "zip";
    if (function_exists("gzopen"))
    	$ar_comps['gzip'] = "gzip";

	echo "<tr><td class='label'>$label</td><td>";
	echo array_selector('comp', $value, $ar_comps);
	echo "</td></tr>";
}

function download_file($filename)
{
    if (empty($filename) || !file_exists($filename))
    {
		UiMessageService::displayError(_('Select backup file first.'));
        return false;
    }
    $saveasname = basename($filename);
    header('Content-type: application/octet-stream');
   	header('Content-Length: '.filesize($filename));
   	header('Content-Disposition: attachment; filename="'.$saveasname.'"');
    readfile($filename);

    return true;
}

$conn = $db_connections[user_company()];
$backup_name = clean_file_name(RequestService::getPostStatic('backups'));
$backup_path = $SysPrefs->backup_dir() . $backup_name;

if (RequestService::getPostStatic('creat')) {
	generate_backup($conn, RequestService::getPostStatic('comp'), RequestService::getPostStatic('comments'));
	$Ajax->activate('backups');
	$SysPrefs->refresh(); // re-read system setup
};

if (RequestService::getPostStatic('restore')) {
	if ($backup_name) {
	if (db_import($backup_path, $conn, true, false, RequestService::checkValueStatic('protect')))
		\FA\Services\UiMessageService::displayNotification(_(UI_TEXT_RESTORE_BACKUP_COMPLETED));
	$SysPrefs->refresh(); // re-read system setup
} else
	UiMessageService::displayError(_(UI_TEXT_SELECT_BACKUP_FILE_FIRST));
}

if (RequestService::getPostStatic('deldump')) {
	if ($backup_name) {
		if (unlink($backup_path)) {
			\FA\Services\UiMessageService::displayNotification(_(UI_TEXT_FILE_SUCCESSFULLY_DELETED)." "
					. _(UI_TEXT_FILENAME) . ": " . $backup_name);
			$Ajax->activate('backups');
		}
		else
			UiMessageService::displayError(_(UI_TEXT_CANT_DELETE_BACKUP_FILE));
	} else
		UiMessageService::displayError(_(UI_TEXT_SELECT_BACKUP_FILE_FIRST));
}

if (RequestService::getPostStatic('upload'))
{
	$tmpname = $_FILES['uploadfile']['tmp_name'];
	$fname = trim(basename($_FILES['uploadfile']['name']));

	if ($fname) {
	if (!preg_match("/\.sql(\.zip|\.gz)?$/", $fname))
		UiMessageService::displayError(_(UI_TEXT_YOU_CAN_ONLY_UPLOAD_SQL_BACKUP_FILES));
	elseif ($fname != clean_file_name($fname))
		UiMessageService::displayError(_(UI_TEXT_FILENAME_CONTAINS_FORBIDDEN_CHARS));
	elseif (is_uploaded_file($tmpname)) {
		rename($tmpname, $SysPrefs->backup_dir() . $fname);
		\FA\Services\UiMessageService::displayNotification(_(UI_TEXT_FILE_UPLOADED_TO_BACKUP_DIRECTORY));
		$Ajax->activate('backups');
	} else
		UiMessageService::displayError(_(UI_TEXT_FILE_WAS_NOT_UPLOADED_INTO_THE_SYSTEM));
} else
	UiMessageService::displayError(_(UI_TEXT_SELECT_BACKUP_FILE_FIRST));}
//-------------------------------------------------------------------------------
start_form(true, true);
start_outer_table(TABLESTYLE2);
table_section(1);
table_section_title(_(UI_TEXT_CREATE_BACKUP));
	textarea_row(_(UI_TEXT_COMMENTS_LABEL), 'comments', null, 30, 8);
	compress_list_row(_(UI_TEXT_COMPRESSION_LABEL),'comp');
	vertical_space("height='20px'");
	submit_row('creat',_(UI_TEXT_CREATE_BACKUP), false, "colspan=2 align='center'", '', 'process');
table_section(2);
table_section_title(_(UI_TEXT_BACKUP_SCRIPTS_MAINTENANCE));

	start_row();
	echo "<td style='padding-left:20px' align='left'>".get_backup_file_combo()."</td>";
	echo "<td style='padding-left:20px' valign='top'>";
	start_table();
	submit_row('view',_(UI_TEXT_VIEW_BACKUP), false, '', '', false);
	submit_row('download',_(UI_TEXT_DOWNLOAD_BACKUP), false, '', '', 'download');
	submit_row('restore',_(UI_TEXT_RESTORE_BACKUP), false, '','', 'process');
	submit_js_confirm('restore',_(UI_TEXT_YOU_ARE_ABOUT_TO_RESTORE_DATABASE_FROM_BACKUP_FILE));

	submit_row('deldump', _(UI_TEXT_DELETE_BACKUP), false, '','', true);
	// don't use 'delete' name or IE js errors appear
	submit_js_confirm('deldump', sprintf(_(UI_TEXT_YOU_ARE_ABOUT_TO_REMOVE_SELECTED_BACKUP_FILE)));
	end_table();
	echo "</td>";
	end_row();
start_row();
	echo "<td style='padding-left:20px'  cspan=2>"
	. radio(_('Update security settings'), 'protect', 0) . '<br>'
	. radio(_('Protect security settings'), 'protect', 1, true) . "</td>";
end_row();
start_row();
	echo "<td style='padding-left:20px' align='left'><input name='uploadfile' type='file'></td>";
	submit_cells('upload',_(UI_TEXT_UPLOAD_FILE),"style='padding-left:20px'", '', true);
end_row();
end_outer_table();

end_form();

end_page();
