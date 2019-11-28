<?php

    // specify the REST web service to interact with
    $url = 'http://{sugar_url}/service/v4_1/rest.php';

    // Open a SugarHttpClient session for making the call
    require_once('include/SugarHttpClient.php');

    $client = new SugarHttpClient;

    // Set the POST arguments to pass to the Sugar server
    $parameters = array(
        'user_auth' => array(
            'user_name' => 'username',
            'password' => md5('password'),
        ),
    );
    
    $json = json_encode($parameters);   
    $postArgs = array(
        'method' => 'login',
        'input_type' => 'JSON',
        'response_type' => 'JSON',
        'rest_data' => $json,
    );

    $postArgs = http_build_query($postArgs);

    // Make the REST call, returning the result
    $response = $client->callRest($url, $postArgs);
    
    if ( $response === false )
    {
        die("Request failed.\n");
    }

    // Convert the result from JSON format to a PHP array
    $result = json_decode($response);

    if ( !is_object($result) )
    {
        die("Error handling result.\n");
    }
    
    if ( !isset($result->id) )
    {
        die("Error: {$result->name} - {$result->description}\n.");
    }

    // Get the session id
    $sessionId = $result->id;
    
?>
