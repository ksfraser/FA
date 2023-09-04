<?php

require_once( 'includes.inc' );
require_once( 'Client.php' );

$consumerKey = "ck_8e5fa14694641f738795e774e15ec34a67ac21bc";
$consumerSecret = "cs_b3535907b3d42aad5569585b70bfa00ad598e799";
$url = "http://mickey.ksfraser.com/devel/fhs/wordpress/";
//$url = "https://mickey.ksfraser.com/devel/fhs/wordpress/";
$options = array();

use  Automattic\WooCommerce\Client;

$test = new Client($url, $consumerKey, $consumerSecret, $options ) ;

$response = "";
var_dump( $response );
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
];
$response = $test->post( $endpoint, $data );
var_dump( $response );


//$endpoint = "";
//$data = "";
$response = $test->put( $endpoint, $data );
var_dump( $response );


//$endpoint = "";
//$data = "";
$response = $test->get( $endpoint, $data );
var_dump( $response );


//$endpoint = "";
//$data = "";
$response = $test->delete( $endpoint, $data );
var_dump( $response );


//$endpoint = "";
//$data = "";
$response = $test->options( $endpoint );
var_dump( $response );


