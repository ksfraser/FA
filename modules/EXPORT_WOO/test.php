<?php

// Install:
// composer require automattic/woocommerce

// Setup:
require __DIR__ . '/vendor/autoload.php';

use Automattic\WooCommerce\Client;

/*
$consumerKey = "ck_8e5fa14694641f738795e774e15ec34a67ac21bc";
$consumerSecret = "cs_b3535907b3d42aad5569585b70bfa00ad598e799";
*/

$consumerKey = "ck_49dcd21151f9c08d30aacba2ee82ec21cfb2cad3";
$consumerSecret = "cs_3687a611ac720a3976d1bba362222f42065367dc";
$url = "http://mickey.ksfraser.com/devel/fhs/wordpress/index.php";
	//Need the index.php since .htaccess changes didn't work
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
//        print_r($woocommerce->get(''));
} catch( Exception $e )
{
 //       var_dump( $e );
}

/*
$endppoint = 'coupons';
$data = [
    'code' => '10off',
    'discount_type' => 'percent',
    'amount' => '10',
    'individual_use' => true,
    'exclude_sale_items' => true,
    'minimum_amount' => '100.00'
];
*/

$test = new Client($url, $consumerKey, $consumerSecret, $options ) ;

//$response = "";
//var_dump( $response );
$endpoint = "products";
$data = [
    'name' => 'Premium Quality',
    'type' => 'simple',
    'regular_price' => '21.99',
    'description' => 'Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.',
    'short_description' => 'Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.',
    'categories' => [
        [
            'id' => 9
        ],
        [
            'id' => 14
        ]
    ],
    'images' => [
        [
            'src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_front.jpg'
        ],
        [
            'src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_back.jpg'
        ],
	[
		'src' => 'http://fraserhighlandshoppe.ca/Media/front/D-Pad12.jpg'
		//'src' => 'http://192.168.1.65/devel/fhs/frontaccounting/company/1/images/D-Pad12.jpg'
	]
    ] 
];
$data2 = [
 	'name' => 'Drum',
	'type' => 'simple',
    	'regular_price' => '21.99',
	'description' => '',
];
$data3 = [
 	'name' => 'Bagpipes',
	'type' => 'variable',
    	'regular_price' => '21.99',
	'description' => '',
];

print_r( parse_url('http://192.168.1.65/devel/fhs/frontaccounting/company/1/images/D-Pad12.jpg' ) );
print_r( parse_url( 'http://fhsws001.ksfraser.com/devel/fhs/frontaccounting/company/1/images/D-Pad12.jpg' ) );
print_r( parse_url( 'http://fraserhighlandshoppe.ca/Media/front/D-Pad12.jpg' ) );
exit;

echo "---------------------------------------------\n\n";
echo "\n\n Creating a product \n\n";
$response = $test->post( $endpoint, $data );
var_dump( $response );
echo "\n\n Creating a product \n\n";
$response = $test->post( $endpoint, $data2 );
var_dump( $response );
echo "\n\n Creating a product \n\n";
$response = $test->post( $endpoint, $data3 );
var_dump( $response );
echo "---------------------------------------------\n\n";


/*
stdClass Object
(
    [id] => 24
    [name] => Premium Quality
    [slug] => premium-quality
    [permalink] => http://mickey.ksfraser.com/devel/fhs/wordpress/product/premium-quality/
    [date_created] => 2019-11-27T01:32:53
    [date_created_gmt] => 2019-11-27T01:32:53
    [date_modified] => 2019-11-27T01:32:53
    [date_modified_gmt] => 2019-11-27T01:32:53
    [type] => simple
    [status] => publish
    [featured] =>
    [catalog_visibility] => visible
    [description] => Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.
    [short_description] => Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.
    [sku] =>
    [price] => 21.99
    [regular_price] => 21.99
    [sale_price] =>
    [date_on_sale_from] =>
    [date_on_sale_from_gmt] =>
    [date_on_sale_to] =>
    [date_on_sale_to_gmt] =>
    [price_html] => <span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">&#36;</span>21.99</span>
    [on_sale] =>
    [purchasable] => 1
    [total_sales] => 0
    [virtual] =>
    [downloadable] =>
    [downloads] => Array
        (
        )

    [download_limit] => -1
    [download_expiry] => -1
    [external_url] =>
    [button_text] =>
    [tax_status] => taxable
    [tax_class] =>
    [manage_stock] =>
    [stock_quantity] =>
    [stock_status] => instock
    [backorders] => no
    [backorders_allowed] =>
    [backordered] =>
    [sold_individually] =>
    [weight] =>
    [dimensions] => stdClass Object
        (
            [length] =>
            [width] =>
            [height] =>
        )

    [shipping_required] => 1
    [shipping_taxable] => 1
    [shipping_class] =>
    [shipping_class_id] => 0
    [reviews_allowed] => 1
    [average_rating] => 0
    [rating_count] => 0
    [related_ids] => Array
        (
        )

    [upsell_ids] => Array
        (
        )

    [cross_sell_ids] => Array
        (
        )

    [parent_id] => 0
    [purchase_note] =>
    [categories] => Array
        (
            [0] => stdClass Object
                (
                    [id] => 15
                    [name] => Uncategorized
                    [slug] => uncategorized
                )

        )

    [tags] => Array
        (
        )

    [images] => Array
        (
            [0] => stdClass Object
                (
                    [id] => 22
                    [date_created] => 2019-11-27T01:32:50
                    [date_created_gmt] => 2019-11-27T01:32:50
                    [date_modified] => 2019-11-27T01:32:50
                    [date_modified_gmt] => 2019-11-27T01:32:50
                    [src] => http://mickey.ksfraser.com/devel/fhs/wordpress/wp-content/uploads/2019/11/T_2_front.jpg
                    [name] => T_2_front.jpg
                    [alt] =>
                )

            [1] => stdClass Object
                (
                    [id] => 23
                    [date_created] => 2019-11-27T01:32:52
                    [date_created_gmt] => 2019-11-27T01:32:52
                    [date_modified] => 2019-11-27T01:32:52
                    [date_modified_gmt] => 2019-11-27T01:32:52
                    [src] => http://mickey.ksfraser.com/devel/fhs/wordpress/wp-content/uploads/2019/11/T_2_back.jpg
                    [name] => T_2_back.jpg
                    [alt] =>
                )

        )

    [attributes] => Array
        (
        )

    [default_attributes] => Array
        (
        )

    [variations] => Array
        (
        )

    [grouped_products] => Array
        (
        )

    [menu_order] => 0
    [meta_data] => Array
        (
        )

    [_links] => stdClass Object
        (
            [self] => Array
                (
                    [0] => stdClass Object
                        (
                            [href] => http://mickey.ksfraser.com/devel/fhs/wordpress/wp-json/wc/v3/products/24
                        )

                )

            [collection] => Array
                (
                    [0] => stdClass Object
                        (
                            [href] => http://mickey.ksfraser.com/devel/fhs/wordpress/wp-json/wc/v3/products
                        )

                )

        )

)
*/


//UPDATE is products/ID with data for update
/*
echo "---------------------------------------------\n\n";
echo "\n\n Updating a product \n\n";
//$endpoint = "";
//$data = "";
$response = $test->put( $endpoint, $data );
var_dump( $response );
echo "---------------------------------------------\n\n";
*/

echo "---------------------------------------------\n\n";
echo "\n\n Listing ALL products \n\n";
//LIST ALL is products
//$endpoint = "";
$endpoint="products";
$data = null;
//$data = array( 'search' => 'Premium' );
$response = $test->get( $endpoint );
var_dump( $response );
echo "---------------------------------------------\n\n";


echo "---------------------------------------------\n\n";
echo "\n\n Listing ALL products by SEARCH for 'Drum'\n\n";
$product_id = 56;
//$endpoint = "";
$endpoint="products";
//$data = "";
$data = array( 'search' => 'Drum' );
$response = $test->get( $endpoint, $data );
var_dump( $response );
echo "---------------------------------------------\n\n";



echo "---------------------------------------------\n\n";
$product_id = 66;
echo "\n\n Retrieve product $product_id\n\n";
//RETRIEVE is products/ID
//$endpoint = "";
$endpoint="products/" . $product_id;
//$data = "";
$response = $test->get( $endpoint, $data );
var_dump( $response );
echo "---------------------------------------------\n\n";


echo "---------------------------------------------\n\n";
$product_id = 66;
echo "\n\n DELETE product $product_id\n\n";
//DELETE is products/ID
//$endpoint = "";
//$data = "";
$force = array( 'force' => true );
$response = $test->delete( $endpoint, $force );
var_dump( $response );
echo "---------------------------------------------\n\n";


//$endpoint = "";
//$data = "";
//$response = $test->options( $endpoint );
//var_dump( $response );


