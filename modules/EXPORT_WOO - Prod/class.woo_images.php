<?php

/*******************************************
 * If you change the list of properties below, ensure that you also modify
 * build_write_properties_array appropriately
 * */

/*******************************************************************************************//**
 *
 * TODO:
 *	
 * ***********************************************************************************************/

require_once( 'class.woo_rest.php' );
require_once( 'class.woo_interface.php' );

class woo_image {
	var $src;
	var $position;
	var $image_serverurl;
	var $image_baseurl;
	var $image_name;
	var $stock_id;
	var $pic_num;
	function __construct( $stock_id, $pic_num, $server_url, $base_url )
	{
		$this->stock_id = $stock_id;
		$this->pic_num = $pic_num;
		$this->image_server_url = $server_url;
		$this->image_base_url = $base_url;
		$this->run();
	}
	/*@string@*/function image_exists( $stock_id )
	{
		$filename = company_path().'/images/' . item_img_name($stock_id) . ".jpg";
		if( file_exists( $filename ) === TRUE )
			return item_img_name($stock_id) . ".jpg"; 
		else
			return NULL;
	}
	function run()
	{
		if( isset( $this->image_serverurl ) AND isset( $this->image_baseurl ) )
		{
			if( $this->pic_num > 0 )
				$image_name = $this->image_exists( $this->stock_id . $this->pic_num );
			else
				$image_name = $this->image_exists( $this->stock_id );
			if( null != $image_name )
			{
				$this->src  = $this->client->image_serverurl . '/'  . $this->client->image_baseurl . '/' . $image_name;
				$this->position = $this->pic_num;
			}
		}
		else
		{
			$this->src   = $this->stock_id . '.jpg"';
			$this->position = $this->pic_num;
		}
	}
}

class woo_images {
	var $stock_id;
	var $client;	
	var $image_array;
	function __construct( $stock_id, $client )
	{
		$this->stock_id = $stock_id;
		$this->client = $client;
		return;
	}
	function run()
	{
		return $this->product_images();
	}
	function product_images()
	{
		//IMAGES
		//If we use local URL we need to build it and send it
		//If we need to use WOOCOMMERCE image gallery, we need the filename
		//With the module to allow extra images, we need to check for that too
		////SHould also check for the existance of the filename in the local company
		//	Default location is (/company/0/images
		$image_array = array();
		$imagecount = 0;
		if( isset( $this->client->image_serverurl ) AND isset( $this->client->image_baseurl ) )
		{
			$img = new woo_image($this->stock_id, 0, $this->client->image_serverurl, $this->client->image_baseurl);
			$image_array[$imagecount]['src']  = $img->src;
			$image_array[$imagecount]['position'] = $img->position;
			$imagecount++;

			if( isset( $this->client->maxpics ) )
			{
				for ( $j = 1; $j <= $this->client->maxpics; $j++ )
				{
					$img = new woo_image($this->stock_id, $j, $this->client->image_serverurl, $this->client->image_baseurl);
					$image_array[$imagecount]['src']  = $img->src;
					$image_array[$imagecount]['position'] = $img->position;
					$imagecount++;
				}
			}
		}
		$this->image_array = $image_array;
		return $image_array;
	}
}

?>
