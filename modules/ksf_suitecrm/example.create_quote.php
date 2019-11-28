<?php

    $url = "http://{site_url}/service/v4_1/rest.php";
    $username = "admin";
    $password = "password";

    //function to make cURL request
    function call($method, $parameters, $url)
    {
        ob_start();
        $curl_request = curl_init();

        curl_setopt($curl_request, CURLOPT_URL, $url);
        curl_setopt($curl_request, CURLOPT_POST, 1);
        curl_setopt($curl_request, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($curl_request, CURLOPT_HEADER, 1);
        curl_setopt($curl_request, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_request, CURLOPT_FOLLOWLOCATION, 0);

        $jsonEncodedData = json_encode($parameters);

        $post = array(
            "method" => $method,
            "input_type" => "JSON",
            "response_type" => "JSON",
            "rest_data" => $jsonEncodedData
        );

        curl_setopt($curl_request, CURLOPT_POSTFIELDS, $post);
        $result = curl_exec($curl_request);
        curl_close($curl_request);

        $result = explode("\r\n\r\n", $result, 2);
        $response = json_decode($result[1]);
        ob_end_flush();

        return $response;
    }

    //login ---------------------------------------------- 
    $login_parameters = array(
        "user_auth" => array(
            "user_name" => $username,
            "password" => md5($password),
            "version" => "1"
        ),
        "application_name" => "RestTest",
        "name_value_list" => array(),
    );

    $login_result = call("login", $login_parameters, $url);

    /*
    echo "<pre>";
    print_r($login_result);
    echo "</pre>";
    */

    //get session id
    $session_id = $login_result->id;

    //create quote ---------------------------------------------- 
    $createQuoteParams = array(
        'session' => $session_id,
        'module_name' => 'Quotes',
        'name_value_list' => array(
            array(
                'name' => 'name',
                'value' => 'Widget Quote'
            ),
            array(
                'name' => 'team_count',
                'value' => ''
            ),
            array(
                'name' => 'team_name',
                'value' => ''
            ),
            array(
                'name' => 'date_quote_expected_closed',
                'value' => date('Y-m-d', mktime(0, 0, 0, date('m')  , date('d')+7, date('Y')))
            ),
            array(
                'name' => 'quote_stage',
                'value' => 'Negotiation'
            ),
            array(
                'name' => 'quote_num',
                'value' => ''
            ),
            array(
                'name' => 'quote_type',
                'value' => 'Quotes'
            ),
            array(
                'name' => 'subtotal',
                'value' => '1230.23'
            ),
            array(
                'name' => 'subtotal_usdollar',
                'value' => '1230.23'
            ),
        ),
    );

    $createQuoteResult = call('set_entry', $createQuoteParams, $url);

    echo "Create Quote Result<br />";
    echo "<pre>";
    print_r($createQuoteResult);
    echo "</pre>";

    //create product ---------------------------------------------- 
    $createProductParams = array(
        'session' => $session_id,
        'module_name' => 'Products',
        'name_value_list' => array(
            array(
                'name' => 'name',
                'value' => 'Widget'
            ),
            array(
                'name' => 'quote_id',
                'value' => $createQuoteResult->id
            ),
            array(
                'name' => 'status',
                'value' => 'Quotes'
            )
        )
    );

    $createProductResult = call('set_entry', $createProductParams, $url);

    echo "Create Product Result<br />";
    echo "<pre>";
    print_r($createProductResult);
    echo "</pre>";

    //create product-bundle ---------------------------------------------- 
    $createProductBundleParams = array(
        "session"         => $session_id,
        "module_name"     => "ProductBundles",
        "name_value_list" => array(
            array(
                'name' => 'name',
                'value' => 'Rest SugarOnline Order'),
            array(
                'name' => 'bundle_stage',
                'value' => 'Draft'
            ),
            array(
                'name' => 'tax',
                'value' => '0.00'
            ),
            array(
                'name' => 'total',
                'value' => '0.00'
            ),
            array(
                'name' => 'subtotal',
                'value' => '0.00'
            ),
            array(
                'name' => 'shipping',
                'value' => '0.00'
            ),
            array(
                'name' => 'currency_id',
                'value' => '-99'
            ),
        )
    );

    $createProductBundleResult = call('set_entry', $createProductBundleParams, $url);

    echo "Create ProductBundles Result<br />";
    echo "<pre>";
    print_r($createProductBundleResult);
    echo "</pre>";

    //relate product to product-bundle ---------------------------------------- 
    $relationshipProductBundleProductsParams = array(
        'session' => $session_id,
        'module_name' => 'ProductBundles',
        'module_id' => $createProductBundleResult->id,
        'link_field_name' => 'products',
        'related_ids' => array(
            $createProductResult->id
        ),
    );

    // set the product bundles products relationship
    $relationshipProductBundleProductResult = call('set_relationship', $relationshipProductBundleProductsParams, $url);

    echo "Create ProductBundleProduct Relationship Result<br />";
    echo "<pre>";
    print_r($relationshipProductBundleProductResult);
    echo "</pre>";

    //relate product-bundle to quote ---------------------------------------- 
    $relationshipProductBundleQuoteParams = array(
        'session' => $session_id,
        'module_name' => 'Quotes',
        'module_id' => $createQuoteResult->id,
        'link_field_name' => 'product_bundles',
        'related_ids' => array(
            $createProductBundleResult->id
        ),
        'name_value_list' => array()
    );

    // set the product bundles quotes relationship
    $relationshipProductBundleQuoteResult = call('set_relationship', $relationshipProductBundleQuoteParams, $url);

    echo "Create ProductBundleQuote Relationship Result<br />";
    echo "<pre>";
    print_r($relationshipProductBundleQuoteResult);
    echo "</pre>";
