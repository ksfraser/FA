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

    //login ----------------------------------------------------- 
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

    //create document -------------------------------------------- 
    $set_entry_parameters = array(
        //session id
        "session" => $session_id,

        //The name of the module
        "module_name" => "Documents",

        //Record attributes
        "name_value_list" => array(
            //to update a record, pass in a record id as commented below
            //array("name" => "id", "value" => "9b170af9-3080-e22b-fbc1-4fea74def88f"),
            array("name" => "document_name", "value" => "Example Document"),
            array("name" => "revision", "value" => "1"),
        ),
    );

    $set_entry_result = call("set_entry", $set_entry_parameters, $url);

    echo "<pre>";
    print_r($set_entry_result);
    echo "</pre>";

    $document_id = $set_entry_result->id;

    //create document revision ------------------------------------ 
    $contents = file_get_contents ("/path/to/example_document.txt");

    $set_document_revision_parameters = array(
        //session id
        "session" => $session_id,

        //The attachment details
        "note" => array(
            //The ID of the parent document.
            'id' => $document_id,

            //The binary contents of the file.
            'file' => base64_encode($contents),

            //The name of the file
            'filename' => 'example_document.txt',

            //The revision number
            'revision' => '1',
        ),
    );

    $set_document_revision_result = call("set_document_revision", $set_document_revision_parameters, $url);

    echo "<pre>";
    print_r($set_document_revision_result);
    echo "</pre>";

?>
