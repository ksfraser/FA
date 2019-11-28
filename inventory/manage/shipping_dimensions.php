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

//$page_security = 'SA_ITEMSHIPDIM';
$page_security = 'SA_ITEM';
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/includes/db/connect_db.inc");

//add_access_extensions();
//set_ext_domain('modules/zen_import');

if (!@$_GET['popup'])
{
	if (isset($_GET['stock_id'])){
		page(_($help_context = "Shipping Dimensions"), true);
	} else {
		page(_($help_context = "Shipping Dimensions"));
	}
}
if (isset($_GET['stock_id']))
	$_POST['stock_id'] = $_GET['stock_id'];

class shipdim
{
	var $stock_id;
	var $Length;
	var $Width;
	var $Height;
	var $Weight;
	var $dimweight;
	var $billableweight;
	var $factor;
	var $metricimperial = array();	//metric, imperial
	var $systemofmeasure;
	var $shippers = array();	//different shippers have different rates
	var $shipment_types = array();	
	var $shipment_method;		//what type used
	var $factors = array();
	var $shipper;
	var $units = array();
	var $formrows = array();
	var $tb_pref;
	var $tablename;
	var $dblink;
	var $pic_height;
	var $b_addupdate;	//add (0) or update (1)
	
	function __construct( $TB_PREF, $stock_id )
	{
		display_notification(_("Ship Dim Construct"), true);

		global $db;
		$this->set_var( "dblink", $db );
	
		$this->set_var( "tb_pref", $TB_PREF );
		$this->set_var( "tablename", $TB_PREF . "item_shipdim" );
		$this->set_var( "stock_id", $stock_id );
		$this->set_var( "b_addupdate", 0 );
		$this->set_var( "pic_height", 120 );	//Eventually we want to make this a system pref
		if( $this->check_table() == FALSE )
			$this->create_table();
		$this->Length = $this->Width = $this->Height = $this->Weight = 0;
		$this->dimweight = 0;
		$this->billableweight = 0;
		$this->metricimperial = array( "metric", "imperial" );
		$this->systemofmeasure = "metric";
		$this->shipper = "Canada Post";
		$this->shipment_method = "Ground";
		$this->init_shiprates();
		$this->init_units();
		//$this->factor = 6000;	//Default IATA/Canada Post for domestic (Wikipedia)
		$this->change_factor();
		$this->formrows = array( "Length", "Width", "Height", "Weight" );
		$this->get();	//Load any existing data
	}
	function init_units()
	{
		$this->units["size"]["metric"] = "cm";
		$this->units["size"]["imperial"] = "inches";
		$this->units["Height"]["metric"] = "cm";
		$this->units["Height"]["imperial"] = "inches";
		$this->units["Length"]["metric"] = "cm";
		$this->units["Length"]["imperial"] = "inches";
		$this->units["Width"]["metric"] = "cm";
		$this->units["Width"]["imperial"] = "inches";
		$this->units["weight"]["metric"] = "kgs";
		$this->units["weight"]["imperial"] = "lbs";
		$this->units["Weight"]["metric"] = "kgs";
		$this->units["Weight"]["imperial"] = "lbs";
	}
	function query( $sql, $msg )
	{
		//$res = db_query( $sql, $msg );
		$result = mysql_query( $sql, $this->dblink);
		if( $result === FALSE )
		{
			display_notification( $msg );
			display_notification( $sql );
		}
		return $result;
	}
	function fetch_query(  $sql, $msg )
	{
		$res = $this->query( $sql, $msg );
		if( $res === FALSE )
			return NULL;
		$arr = mysql_fetch_assoc( $res );
		return $arr;
	}
	function set_var( $var, $val )
	{
		display_notification(_("Ship Dim Set_var") . " " .$var ." to " . $val, true);
		$this->$var = $val;
		$this->notify( $var );
	}
	function get_var( $var )
	{
		display_notification(_("Ship Dim getvar"), true);
		return $this->$var;
	}
	function notify( $var )
	{
		//display_notification(_("Ship Dim notify"), true);
		//If we change certain values, we need to recalculate things
		if ( $var == "systemofmeasure" )
			$this->change_factor();
	}
	/*bool*/ function check_table()
	{
		display_notification(_("Ship Dim Checktable"), true);
		$sql = "SHOW TABLES LIKE '%" . $this->tablename . "%'";
		$result = $this->query( $sql, "Could not check table " . $this->tablename );
		if( $result === FALSE )
			return FALSE;
		else
			return TRUE;
	}
	/*bool*/ function create_table()
	{
		display_notification(_("Ship Dim Create table"), true);
		$sql = "CREATE TABLE IF NOT EXISTS `" . $this->tablename . "` ( 
					stock_id varchar(32) NOT NULL default '', 
					Length float NOT NULL default '0.0', 
					Width float NOT NULL default '0.0', 
					Height float NOT NULL default '0.0', 
					Weight float NOT NULL default '0.0', 
					billableweight float NOT NULL default '0.0',
					dimweight float NOT NULL default '0.0',
					units varchar(8) NOT NULL default 'metric',
					PRIMARY KEY (stock_id),
					UNIQUE KEY `stock-units` (`stock_id`,`units`)
					) ENGINE=InnoDB";
		$result = $this->query( $sql, "Could not create table " . $this->tablename );
		if( $result === FALSE )
			return FALSE;
		else
			return TRUE;
	}
	/*bool*/ function change_factor()
	{
		display_notification(_("Ship Dim Change Factor"), true);
		$this->set_var( "factor", $this->factors[$this->shipper][$this->shipment_method][$this->systemofmeasure] );
	}
	/*bool*/ function change_units()
	{
		display_notification(_("Ship Dim Change Unit"), true);
		//2.54 centimeters per inch.  2.2 pounds per kilo
	}
	/*bool*/ function init_shiprates()
	{
		display_notification(_("Ship Dim Init Shiprates"), true);
		$this->shippers = array( "Canada Post", "USPS", "Purolator", "FedEx", "UPS", "DHL" );
		$this->shpment_types = array( "Ground", "Expidited", "Priority", "Express", "US", "International" );

		$this->factors['Canada Post']['Ground']['metric'] = 6000;
		$this->factors['Canada Post']['Expidited']['metric'] = 6000;
		$this->factors['Canada Post']['Priority']['metric'] = 5000;
		$this->factors['Canada Post']['Express']['metric'] = 5000;
		$this->factors['Canada Post']['US']['metric'] = 5000;
		$this->factors['Canada Post']['International']['metric'] = 5000;
		$this->factors['Canada Post']['Ground']['imperial'] = 166;
		$this->factors['Canada Post']['Expidited']['imperial'] = 166;
		$this->factors['Canada Post']['Priority']['imperial'] = 139;
		$this->factors['Canada Post']['Express']['imperial'] = 139;
		$this->factors['Canada Post']['US']['imperial'] = 139;
		$this->factors['Canada Post']['International']['imperial'] = 139;

		$this->factors['DHL']['Ground']['metric'] = 5000;
		$this->factors['DHL']['Expidited']['metric'] = 5000;
		$this->factors['DHL']['Priority']['metric'] = 5000;
		$this->factors['DHL']['Express']['metric'] = 5000;
		$this->factors['DHL']['US']['metric'] = 5000;
		$this->factors['DHL']['International']['metric'] = 5000;
		$this->factors['DHL']['Ground']['imperial'] = 139;
		$this->factors['DHL']['Expidited']['imperial'] = 139;
		$this->factors['DHL']['Priority']['imperial'] = 139;
		$this->factors['DHL']['Express']['imperial'] = 139;
		$this->factors['DHL']['US']['imperial'] = 139;
		$this->factors['DHL']['International']['imperial'] = 139;

		$this->factors['FedEx']['Ground']['metric'] = 6000;
		$this->factors['FedEx']['Expidited']['metric'] = 6000;
		$this->factors['FedEx']['Priority']['metric'] = 6000;
		$this->factors['FedEx']['Express']['metric'] = 6000;
		$this->factors['FedEx']['US']['metric'] = 6000;
		$this->factors['FedEx']['International']['metric'] = 5000;
		$this->factors['FedEx']['Ground']['imperial'] = 166;
		$this->factors['FedEx']['Expidited']['imperial'] = 166;
		$this->factors['FedEx']['Priority']['imperial'] = 166;
		$this->factors['FedEx']['Express']['imperial'] = 166;
		$this->factors['FedEx']['US']['imperial'] = 166;
		$this->factors['FedEx']['International']['imperial'] = 139;
		$this->factors['UPS']['Ground']['metric'] = 6000;
		$this->factors['UPS']['Expidited']['metric'] = 6000;
		$this->factors['UPS']['Priority']['metric'] = 6000;
		$this->factors['UPS']['Express']['metric'] = 6000;
		$this->factors['UPS']['US']['metric'] = 6000;
		$this->factors['UPS']['International']['metric'] = 5000;
		$this->factors['UPS']['Ground']['imperial'] = 166;
		$this->factors['UPS']['Expidited']['imperial'] = 166;
		$this->factors['UPS']['Priority']['imperial'] = 166;
		$this->factors['UPS']['Express']['imperial'] = 166;
		$this->factors['UPS']['US']['imperial'] = 166;
		$this->factors['UPS']['International']['imperial'] = 139;

		return TRUE;
	}
	/*bool*/ function calc_billable_weight()
	{
		display_notification(_("Ship Dim Calc bill"), true);
		/* Following industry practices, the billable product weight will be 
		   the greater of the product's actual weight and its dimensional weight.
                   This calculation is used by both UPS and Canada Post for ground shipments.
		 */
		if( $this->get_var( "dimweight" ) == 0 )
		{
			$this->calc_dim_weight();
		}
		$this->set_var( "billableweight", 
				max( 
					$this->get_var( "Weight" ), 
					$this->get_var( "dimweight" ) 
				) 
			);
		return TRUE;
	}
	/*bool*/ function calc_dim_weight()
	{
		display_notification(_("Ship Dim Calc Dim"), true);
		/*
		   dimensional weight is calculated as the product's volume (length x width x height) 
                   in centimetres cubed divided by 6,000. This provides the dimensional weight in kilograms. 

		   Shipping factors for imperial measurements represent cubic inches per pound (in3/lb) while 
		   metric factors represent cubic centimeters per kilogram (cm3/kg).
		 */
		$this->set_var( "dimweight", 
					$this->get_var( "Length" ) *
					$this->get_var( "Width" ) *
					$this->get_var( "Height" ) / $this->get_var( "factor" )  );
		return TRUE;
	}
	function save()
	{
		display_notification(_("Ship Dim Save"), true);
		$countsql = "select count(*) from " . $this->tablename . " WHERE stock_id = "  . db_escape( $this->stock_id );
		$res = db_query($countsql, "Shipping Dimensions could not be counted" );
		if( $res == FALSE )
		{
			$this->add();
		}
		else
		{
			if( $this->b_addupdate )
				$this->update();
			else
				$this->add();
		}
	}
	function add()
	{
		display_notification(_("Ship Dim add"), true);
		$sql = "INSERT into " . $this->tablename . " ( stock_id, Length, Width, Weight, Height, billableweight, dimweight, units ) values(
				" . db_escape( $this->stock_id ) . ",
				" . db_escape( $this->Length ) . ",
				" . db_escape( $this->Width )  . ",
				" . db_escape( $this->Weight )  . ",
				" . db_escape( $this->Height )  . ",
				" . db_escape( $this->billableweight )  . ",
				" . db_escape( $this->dimweight )  . ",
				" . db_escape( $this->systemofmeasure )  . ");";
        	$this->query( $sql, "Shipping Dimensions could not be added" );
        	//db_query($sql, "Shipping Dimensions could not be updated" );
	}
	function update()
	{
		display_notification(_("Ship Dim update"), true);
		$sql = "UPDATE " . $this->tablename . " SET Length=" . db_escape( $this->Length ) . ",
				Width=" . db_escape( $this->Width )  . ",
				Weight=" . db_escape( $this->Weight )  . ",
				Height=" . db_escape( $this->Height )  . ",
				billableweight=" . db_escape( $this->billableweight )  . ",
				dimweight=" . db_escape( $this->dimweight )  . ",
				units=" . db_escape( $this->systemofmeasure )  . "
				WHERE stock_id=" . db_escape( $this->stock_id );
        	$this->query( $sql, "Shipping Dimensions could not be updated" );
        	//db_query($sql, "Shipping Dimensions could not be updated" );

	}
	function get()
	{
		display_notification(_("Ship Dim get"), true);
		$sql = "SELECT * from " . $this->tablename . "
			WHERE stock_id = "  . db_escape( $this->stock_id );
		$arr = $this->fetch_query( $sql, "Shipping Dimensions could not be fetched" );
		display_notification( $sql, true);

		if ( $arr != FALSE )
		{
			$this->set_var( "Weight", $arr['Weight'] );
			$this->set_var( "Width", $arr['Width'] );
			$this->set_var( "Height", $arr['Height'] );
			$this->set_var( "Length", $arr['Length'] );
			$this->set_var( "systemofmeasure", $arr['units'] );
			if( $arr['dimweight'] > 0 )
			{
				$this->set_var( "dimweight", $arr['dimweight'] );
			$this->set_var( "billableweight", $arr['billableweight'] );
			}
			else
			{
				$this->calc_billable_weight();
			}
			$this->set_var( "b_addupdate", 1 );
		}
		else
		{
			$this->set_var( "b_addupdate", 0 );
		}
	}
	function run()
	{
		global $Ajax;
		display_notification(_("Ship Dim run"), true);
		if (!@$_GET['popup'])
		{
			start_page(@$_GET['popup'], false, false);
		}	
		//This should be a hidden form value so you know a submit was done...
		//if (isset($_POST['updateweight']))
		//{
			//var_dump( $_POST );
			foreach ($this->formrows as $row )
			{
				if( isset( $_POST[$row] ) AND $_POST[$row] != "" )
				{
					$this->set_var( $row, $_POST[$row] );
					unset( $_POST[$row] );
				}
			}
			$this->calc_billable_weight();
			$this->save();
		//}
		$this->display_form();
		//$Ajax->activate('shipdim');
		if (!@$_GET['popup'])
		{
			end_page(@$_GET['popup'], false, false);
		}	
	}
	function display_form()
	{
		display_notification(_("Ship Dim display form"), true);
		start_form();
		$this->display_itemlist();
		echo "<br>";
		echo "<hr></center>";
		$this->display_div_shipdim();
		$this->display_div_control();
		end_form();
	}
	function display_itemlist()
	{
		display_notification(_("Ship Dim display itemlist"), true);
		if (!@$_GET['popup'])
		{
			echo "<center> " . _("Item:"). " ";
			echo stock_costable_items_list('stock_id', $this->stock_id, false, true);
		}	
	}
	function display_div_shipdim()
	{
		display_notification(_("Ship Dim display div shipdim"), true);
		div_start('shipdim');
		start_table(TABLESTYLE);
			$filename = $this->gen_filename( "" );
			$altname = item_img_name($this->stock_id) . ".jpg";
		        if ( file_exists( $filename ) )
		        {
		         //rand() call is necessary here to avoid caching problems. (from items.php)
		                $tbl_img_link = "<img id='item_img' alt = '[" . $altname .
		                        "]' src='" . $filename . "'"." height='" . $this->pic_height . "' border='0'>";
					label_cell( $tbl_img_link );
		        }
		        else
		        {
		                $tbl_img_link = _("No image");
				label_cell( $tbl_img_link );
		        }
			end_row();
			$rowcount = count( $this->formrows );
			for( $rownum = 0; $rownum < $rowcount; $rownum++ )
			{
				//amount_row($label, $name, $init=null, $params=null, $post_label=null, $dec=null)
		 		amount_row( $this->formrows[$rownum] . " " . $this->units[$this->formrows[$rownum]][$this->systemofmeasure], 
					$this->formrows[$rownum], null, "class='tableheader2'", null, 2);
			}
		
			label_cell( "Dimensional Weight" );
			label_cell( $this->dimweight );
			end_row();
			label_cell( "Billable Weight" );
			label_cell( $this->billableweight );
			end_row();
/*
		        echo "<tr><td class='label'>"Sytem of Measure"</td>";
        		echo "<td>";
			return combo_input( "Sytem of Measure", $selected_id, $sql, "systemofmeasure", "systemofmeasure",
        			array(
        			        'select_submit'=> true,
        			        'default' => 'metric',
        			        'async' => false
        			) );
        		echo "</td>\n";
        		echo "</tr>\n";
*/

			//foreach( $this->units => $unit )
			hidden( 'stock_id', $this->stock_id );
			end_row();
			label_cell( 'stock_id' );
			label_cell( $this->stock_id );
			end_row();
			hidden( 'stock_id_update', $this->stock_id );
			//hidden( 'NewStockID', $this->stock_id );	//Needed for error messages about missing values in items.php
			end_row();
		//submit_center_first('updateweight', _("Add Or Update Shipping Dimensions"), '', @$_REQUEST['popup'] ? true : 'default');
		end_table();
		div_end();
	}
	function display_div_control()
	{
		display_notification(_("Ship Dim display div control"), true);
		div_start('controls2');
		submit_center_first('updateweight', _("Add Or Update Shipping Dimensions"), '', @$_REQUEST['popup'] ? true : 'default');
		div_end();
	}
	/*string*/ function gen_filename(/*char*/ $count )
	{
		display_notification(_("Ship Dim gen filename"), true);
		$filename = company_path().'/images/' . item_img_name($this->stock_id) . $count . ".jpg";
		return $filename;
	}
	/*bool*/ function check_file_exists( $filename )
	{
		display_notification(_("Ship Dim check file exist"), true);
	
	        if ( file_exists( $filename ) )
			return TRUE;
		else
			return FALSE;
	}
}

//var_dump( $_SESSION );
//var_dump( $db_connections );

$cu = $_SESSION['wa_current_user'];
$compnum = $cu->company;

	var_dump( $_POST );


if( isset($_POST['stock_id_update']) )
{
	if( $_POST['stock_id_update'] != $_POST['stock_id'] )
	{
		$_POST['stock_id'] = $_POST['stock_id_update'];
	}
	unset( $_POST['_stock_id_edit'] );
	unset( $_POST['stock_id_update'] );
}
else if( isset($_POST['_stock_id_edit']) )
{
	if( $_POST['_stock_id_edit'] != $_POST['stock_id'] )
	{
		$_POST['stock_id'] = $_POST['_stock_id_edit'];
		unset( $_POST['_stock_id_edit'] );
		unset( $_POST['stock_id_update'] );
		unset( $_POST['_stock_id_update'] );
		set_global_stock_item($_POST['stock_id']);
		//$_POST['Length'] = "";
		//$_POST['Height'] = "";
		//$_POST['Width'] = "";
		//$_POST['Weight'] = "";
		
	}
}
	var_dump( $_POST );

//if( !isset($_POST['stock_id']) )
//{
//	$_POST['stock_id'] = get_global_stock_item();
//}

//set_global_stock_item($_POST['stock_id']);

//----------------------------------------------------------------------------------------------------
$shipdim = new shipdim( $db_connections[$compnum]['tbpref'], $_POST['stock_id'] );
//unset( $_POST['stock_id'] );
$shipdim->run();
$Ajax->activate('shipdim');
	var_dump( $_POST );

//var_dump( $shipdim );

/**********************************************************************************************************/

//function radio($label, $name, $value, $selected=null, $submit_on_change=false)
//amount_row($label, $name, $init=null, $params=null, $post_label=null, $dec=null)


?>
