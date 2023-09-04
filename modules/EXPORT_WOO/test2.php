<?php
// Install:
// composer require automattic/woocommerce

// Setup:
require __DIR__ . '/vendor/autoload.php';

use Automattic\WooCommerce\Client;

$consumerKey = "ck_49dcd21151f9c08d30aacba2ee82ec21cfb2cad3";
$consumerSecret = "cs_3687a611ac720a3976d1bba362222f42065367dc";
$url = "http://mickey.ksfraser.com/devel/fhs/wordpress/index.php";
//$url = "https://mickey.ksfraser.com/devel/fhs/wordpress/";
$options = array(
        'wp_api' => true, // Enable the WP REST API integration
        'version' => 'wc/v3', // WooCommerce WP REST API version
	'query_string_auth' => true // Force Basic Authentication as query string true and using under HTTPS
);

/*
$consumerKey = "ck_5c23bccb6043af01a5cf793104842cdf8dc8fd95";
$consumerSecret = "cs_c767a45f69ddc4cec3a4aa46043b00873126488c";
$url = "https://shop.fraserhighlandshoppe.ca/";
	//works
*/

$woocommerce = new Client(
	$url,
	$consumerKey,
	$consumerSecret,
	$options
);

try {
	print_r($woocommerce->get(''));
} catch( Exception $e )
{
	var_dump( $e );
}

$data = [
    'code' => '10off',
    'discount_type' => 'percent',
    'amount' => '10',
    'individual_use' => true,
    'exclude_sale_items' => true,
    'minimum_amount' => '100.00'
];

print_r($woocommerce->post('coupons', $data));
?>
