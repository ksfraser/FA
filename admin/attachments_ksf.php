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
$path_to_root="..";
$page_security = 'SA_ATTACHDOCUMENT';

include_once($path_to_root . "/includes/db_pager.inc");
include_once($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/admin/db/attachments_db.inc");
include_once($path_to_root . "/admin/db/transactions_db.inc");

if (isset($_GET['vw']))
	$view_id = $_GET['vw'];
else
	$view_id = find_submit('view');
if ($view_id != -1)
{
	$row = get_attachment($view_id);
	if ($row['filename'] != "")
	{
		if(in_ajax()) {
			$Ajax->popup($_SERVER['PHP_SELF'].'?vw='.$view_id);
		} else {
			$type = ($row['filetype']) ? $row['filetype'] : 'application/octet-stream';	
    		header("Content-type: ".$type);
    		header('Content-Length: '.$row['filesize']);
	    	//if ($type == 'application/octet-stream')
    		//	header('Content-Disposition: attachment; filename='.$row['filename']);
    		//else
	 			header("Content-Disposition: inline");
	    	echo file_get_contents(company_path(). "/attachments/".$row['unique_name']);
    		exit();
		}
	}	
}
if (isset($_GET['dl']))
	$download_id = $_GET['dl'];
else
	$download_id = find_submit('download');

if ($download_id != -1)
{
	$row = get_attachment($download_id);
	if ($row['filename'] != "")
	{
		if(in_ajax()) {
			$Ajax->redirect($_SERVER['PHP_SELF'].'?dl='.$download_id);
		} else {
			$type = ($row['filetype']) ? $row['filetype'] : 'application/octet-stream';	
    		header("Content-type: ".$type);
	    	header('Content-Length: '.$row['filesize']);
    		header('Content-Disposition: attachment; filename='.$row['filename']);
    		echo file_get_contents(company_path()."/attachments/".$row['unique_name']);
	    	exit();
		}
	}	
}

$js = "";
if ($use_popup_windows)
	$js .= get_js_open_window(800, 500);
page(_($help_context = "Attach Documents"), false, false, "", $js);

simple_page_mode(true);

/***//************************************
* Make sure the Attachments directory exists.  Create if not.
*
* @returns string path of directory
****************************************/
function check_make_attachment_dir()
{
	$dir =  company_path()."/attachments";
	if (!file_exists($dir))
	{
		mkdir ($dir,0777);
		$index_file = "<?php\nheader(\"Location: ../index.php\");\n?>";
		$fp = fopen($dir."/index.php", "w");
		fwrite($fp, $index_file);
		fclose($fp);
	}
	return $dir;
}

class ksf_file_upload_attachment extends ksf_file_upload
{
/***INHERITED
	protected $fp;  //!< @var handle File Pointer
        protected $filename;    //!< @var string name of output file
        protected $tmp_dir;     //!< @var string temporary directory name
        protected $path;        //!<DIR where are the images stored.  default company/X/images...
        protected $filesize;    //!<int
        protected $filepath;    //!<string full path
        protected $filecontents;        //!<binary file contents.
	protected $filetype;	//!< MIME type
        protected $upload_ok;
        protected $files_array;         //!< array List of filenames of files we uploaded
        protected $filepaths_array;     //!< array List of full path filenames of files we uploaded
        protected $ui_class;            //!< class that has the UI screens required for this class to work
        protected $upload_button_name;
        protected $upload_button_label;
        protected $upload_file_field_name;
        protected $b_upload_single_file;
        protected $a_data;              //!< array data returned from file type handler
***/
	protected $tmpname;	//!<string temp name from uploading
        /**//************************************************************************
        * Construct the class that handles a file upload
        *
        * @param pointer _FILES from server
        * @returns NONE
        ****************************************************************************/
        function __construct( $file_global )
	{
		//$content = base64_encode(file_get_contents($_FILES['filename']['tmp_name']));
		$this->set( "tmpname", $_FILES['filename']['tmp_name'] );
		$filename = basename($_FILES['filename']['name']);
		parent::__construct( $filename, null, "import_files", true );
		$this->set( "filesize", $_FILES['filename']['size'] );
		$this->set( "filetype", $_FILES['filename']['type'] );
	}
	/**//********************************
	* Move the attached file to the attachments directory
	*
	* @param string destination directory
	* @param string filename in destdir
	*************************************/
	function move_attachment( $destdir, $unique_name )
	{
	
		move_uploaded_file($tihs->get( "tmpname" ), $destdir."/".$unique_name);
	}

}

	

//----------------------------------------------------------------------------------------
if (isset($_GET['filterType'])) // catch up external links
	$_POST['filterType'] = $_GET['filterType'];
if (isset($_GET['trans_no']))
	$_POST['trans_no'] = $_GET['trans_no'];

if ($Mode == 'ADD_ITEM' || $Mode == 'UPDATE_ITEM')
{
	if (!transaction_exists($_POST['filterType'], $_POST['trans_no']))
		display_error(_("Selected transaction does not exists."));
	elseif ($Mode == 'ADD_ITEM' && (!isset($_FILES['filename']) || $_FILES['filename']['size'] == 0))
		display_error(_("Select attachment file."));
	else {
		$dir = check_make_attachment_dir();
/*
		$dir =  company_path()."/attachments";
		if (!file_exists($dir))
		{
			mkdir ($dir,0777);
			$index_file = "<?php\nheader(\"Location: ../index.php\");\n?>";
			$fp = fopen($dir."/index.php", "w");
			fwrite($fp, $index_file);
			fclose($fp);
		}
*/
		$upfile = new ksf_file_upload_attachment( $_FILES );
/*
		//$content = base64_encode(file_get_contents($_FILES['filename']['tmp_name']));
		$tmpname = $_FILES['filename']['tmp_name'];
		$filename = basename($_FILES['filename']['name']);
		$filesize = $_FILES['filename']['size'];
		$filetype = $_FILES['filename']['type'];
*/

		// file name compatible with POSIX
		// protect against directory traversal
		if ($Mode == 'UPDATE_ITEM')
		{
		    $row = get_attachment($selected_id);
		    if ($row['filename'] == "")
        		exit();
			$unique_name = $row['unique_name'];
			if ($filename && file_exists($dir."/".$unique_name))
				unlink($dir."/".$unique_name);
		}
		else
			$unique_name = uniqid('');

		//save the file
/*
		move_uploaded_file($tmpname, $dir."/".$unique_name);
*/
		$upfile->move_attachment( $dir, $unique_name )

		if ($Mode == 'ADD_ITEM')
		{
			add_attachment($_POST['filterType'], $_POST['trans_no'], $_POST['description'],
				$filename, $unique_name, $filesize, $filetype);
			display_notification(_("Attachment has been inserted.")); 
		}
		else
		{
			update_attachment($selected_id, $_POST['filterType'], $_POST['trans_no'], $_POST['description'],
				$filename, $unique_name, $filesize, $filetype); 
			display_notification(_("Attachment has been updated.")); 
		}
	}
	refresh_pager('trans_tbl');
	$Ajax->activate('_page_body');
	$Mode = 'RESET';
}

if ($Mode == 'Delete')
{
	$row = get_attachment($selected_id);
	$dir =  company_path()."/attachments";
	if (file_exists($dir."/".$row['unique_name']))
		unlink($dir."/".$row['unique_name']);
	delete_attachment($selected_id);	
	display_notification(_("Attachment has been deleted.")); 
	$Mode = 'RESET';
}

if ($Mode == 'RESET')
{
	unset($_POST['trans_no']);
	unset($_POST['description']);
	$selected_id = -1;
}

function viewing_controls()
{
	global $selected_id;
	
    start_table(TABLESTYLE_NOBORDER);

	start_row();
	systypes_list_cells(_("Type:"), 'filterType', null, true);
	if (list_updated('filterType'))
		$selected_id = -1;;

	end_row();
    end_table(1);

}

function trans_view($trans)
{
	return get_trans_view_str($trans["type_no"], $trans["trans_no"]);
}

function edit_link($row)
{
  	return button('Edit'.$row["id"], _("Edit"), _("Edit"), ICON_EDIT);
}

function view_link($row)
{
  	return button('view'.$row["id"], _("View"), _("View"), ICON_VIEW);
}

function download_link($row)
{
  	return button('download'.$row["id"], _("Download"), _("Download"), ICON_DOWN);
}

function delete_link($row)
{
  	return button('Delete'.$row["id"], _("Delete"), _("Delete"), ICON_DELETE);
}

function display_rows($type)
{
	$sql = get_sql_for_attached_documents($type);
	$cols = array(
		_("#") => array('fun'=>'trans_view', 'ord'=>''),
	    _("Description") => array('name'=>'description'),
	    _("Filename") => array('name'=>'filename'),
	    _("Size") => array('name'=>'filesize'),
	    _("Filetype") => array('name'=>'filetype'),
	    _("Date Uploaded") => array('name'=>'tran_date', 'type'=>'date'),
	    	array('insert'=>true, 'fun'=>'edit_link'),
	    	array('insert'=>true, 'fun'=>'view_link'),
	    	array('insert'=>true, 'fun'=>'download_link'),
	    	array('insert'=>true, 'fun'=>'delete_link')
	    );	
		$table =& new_db_pager('trans_tbl', $sql, $cols);

		$table->width = "60%";

		display_db_pager($table);
}

//----------------------------------------------------------------------------------------

start_form(true);

viewing_controls();

display_rows($_POST['filterType']);

br(2);

start_table(TABLESTYLE2);

if ($selected_id != -1)
{
	if ($Mode == 'Edit')
	{
		$row = get_attachment($selected_id);
		$_POST['trans_no']  = $row["trans_no"];
		$_POST['description']  = $row["description"];
		hidden('trans_no', $row['trans_no']);
		hidden('unique_name', $row['unique_name']);
		label_row(_("Transaction #"), $row['trans_no']);
	}	
	hidden('selected_id', $selected_id);
}
else
	text_row_ex(_("Transaction #").':', 'trans_no', 10);
text_row_ex(_("Description").':', 'description', 40);
file_row(_("Attached File") . ":", 'filename', 'filename');

end_table(1);

submit_add_or_update_center($selected_id == -1, '', 'process');

end_form();

end_page();

?>
