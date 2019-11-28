<?php
/**********************************************************************
    Copyright (C) Kevin Fraser.
    Kevin grants Copyright (C) to FrontAccounting, LLC.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
//$page_security = 'SA_ITEMPHOTOVIEW';
$page_security = 'SA_ITEM';
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");

if (!@$_GET['popup'])
{
	if (isset($_GET['stock_id'])){
		page(_($help_context = "Inventory Photos"), true);
	} else {
		page(_($help_context = "Inventory Photos"));
	}
}
if (isset($_GET['stock_id']))
	$_POST['stock_id'] = $_GET['stock_id'];
include_once($path_to_root . "/includes/ui.inc");

/************************
*       Use to allow multiple images for a product.
************************/
$maxpics = 10;
$header_count_repeat = 0;	//How many rows before repeating the header.  0 for no repeat

if (isset($_FILES['pic2']) && $_FILES['pic2']['name'] != '')
	upload_file( $maxpics );

//----------------------------------------------------------------------------------------------------

if (!@$_GET['popup'])
	start_form();

if (!isset($_POST['stock_id']))
	$_POST['stock_id'] = get_global_stock_item();

if (!@$_GET['popup'])
{
	echo "<center> " . _("Item:"). " ";
	echo stock_costable_items_list('stock_id', $_POST['stock_id'], false, true);
}	
echo "<br>";

echo "<hr></center>";

set_global_stock_item($_POST['stock_id']);

/**********************************************************************************************************/
div_start('stock_photos');
//DEFAULT image.  Code taken from items.php
//Display the master photo.
	if (check_value('del_image'))
	{
		$filename = company_path().'/images/'.item_img_name($_POST['stock_id']).".jpg";
		//$filename = company_path().'/images/'.item_img_name($_POST['NewStockID']).".jpg";
		if (file_exists($filename))
			unlink($filename);
	}
        $stock_img_link = "";
        $check_remove_image = false;	//Do we need the DELETE check box
        //if (isset($_POST['NewStockID']) && file_exists(company_path().'/images/'
        if ( file_exists(company_path().'/images/'
                .item_img_name($_POST['stock_id']).".jpg"))
                //.item_img_name($_POST['NewStockID']).".jpg"))
        {
                $stock_img_link .= "<img id='item_img' alt = '[".$_POST['stock_id'].".jpg".
                        "]' src='".company_path().'/images/'.item_img_name($_POST['stock_id']).
                        ".jpg'"." height='$pic_height' border='0'>";
        }
        else
        {
                $stock_img_link .= _("No image");
        }
        label_row("&nbsp;", $stock_img_link);
//!items.php
/**********************************************************************************************************/

start_table(TABLESTYLE);
$th = array( 	_("Photo"), 
		_("Delete Image"),
		);
table_header($th);

$k = 0; //row colour counter
$filename = "";

for ( $j = 1; $j <= $maxpics; $j++ )
{
	$odd = $j % 2;
	alt_table_row_color( $odd );
	$filename = "";
	$tbl_img_link = "";

	$count = $j;
	$filename = gen_filename( $count );
	$altname = item_img_name($_POST['stock_id']). $count . ".jpg";
        if ( file_exists( $filename ) )
        {
         //rand() call is necessary here to avoid caching problems. (from items.php)
                $tbl_img_link = "<img id='item_img' alt = '[" . $altname .
                        "]' src='" . $filename . "?nocache=".rand()."'"." height='$pic_height' border='0'>";
		$di = "del_image" . $j;
                if ( isset($_POST[$di]) OR isset( $_GET[$di] ) )
                {
                        if (file_exists($filename))
                                unlink($filename);
                }
		else
		{
			//Doesn't make sense to show the picture and delete message for one we've indicated to delete!
			label_cell( $tbl_img_link );
        		check_row(_("Delete Image " . $j . ":" . $filename), $di);
        		//check_row(_("Delete Image " . $j ), '$di');
		}
        }
        else
        {
                $tbl_img_link = _("No image");
		label_cell( $tbl_img_link );
        }
	end_row();

	if( $header_count_repeat != 0 )
	{
		//Repeat the header if we are mre than _repeat rows
		if ( $j % $header_count_repeat === 0 )
		{
			table_header($th);
		}
	}
}
        file_row(_("Add an Image File (.jpg/.png)") . ":", 'pic2', 'pic2');
	hidden( 'stock_id', $_POST['stock_id'] );
	hidden( 'NewStockID', $_POST['stock_id'] );	//Needed for error messages about missing values in items.php
end_table();
div_end();

div_start('controls2');
	submit_center_first('addupdatephoto', _("Add Or Delete Photos"), '', @$_REQUEST['popup'] ? true : 'default');
div_end();

if (!@$_GET['popup'])
{
	end_form();
	end_page(@$_GET['popup'], false, false);
}	
$Ajax->activate('stock_photos');
/**********************************************************************************************************/

/*string*/ function gen_filename(/*char*/ $count )
{
	$filename = company_path().'/images/' . item_img_name($_POST['stock_id']) . $count . ".jpg";
	return $filename;
}
/*bool*/ function check_file_exists( $filename )
{

        if ( file_exists( $filename ) )
		return TRUE;
	else
		return FALSE;
}
/*string*/ function next_filename( $maxpics )
{
	for ( $j = 1; $j <= $maxpics; $j++ )
	{
		$filename = gen_filename( $j );
		if( check_file_exists( $filename ) === FALSE )
			return $filename;
	}
	return "";
}

function upload_file( $maxpics )
{
	global $Ajax, $max_image_size;
	$bupload_file = "";
	if (isset($_FILES['pic2']) && $_FILES['pic2']['name'] != '')
	{
 	       $result = $_FILES['pic2']['error'];
 	       $bupload_file = 'Yes'; //Assume all is well to start off with
 	       $filepath = company_path().'/images';
 	       if (!file_exists($filepath))
 	       {
 	               mkdir($filepath);
 	       }
 	       $filename = next_filename( $maxpics ); 
		if( $filename == "" )
			return FALSE;

	        //But check for the worst
	        if ((list($width, $height, $type, $attr) = getimagesize($_FILES['pic2']['tmp_name'])) !== false)
	                $imagetype = $type;
	        else
	                $imagetype = false;
	        //$imagetype = exif_imagetype($_FILES['pic2']['tmp_name']);
	        if ($imagetype != IMAGETYPE_GIF && $imagetype != IMAGETYPE_JPEG && $imagetype != IMAGETYPE_PNG)
	        {       //File type Check
	                display_warning( _('Only graphics files can be uploaded'));
	                $bupload_file ='No';
	        }
	        elseif (!in_array(strtoupper(substr(trim($_FILES['pic2']['name']), strlen($_FILES['pic2']['name']) - 3)), array('JPG','PNG','GIF')))
	        {
	                display_warning(_('Only graphics files are supported - a file extension of .jpg, .png or .gif is expected'));
	                $bupload_file ='No';
	        }
	        elseif ( $_FILES['pic2']['size'] > ($max_image_size * 1024))
	        { //File Size Check
	                display_warning(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $max_image_size);
	                $bupload_file ='No';
	        }
	}
        if ($bupload_file == 'Yes')
        {
                $result  =  move_uploaded_file($_FILES['pic2']['tmp_name'], $filename);
        }
        $Ajax->activate('stock_photos');
}

?>
