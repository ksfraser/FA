<?php

    $url = "http://fhsws001/devel/fhs/SuiteCRM/service/v4_1/rest.php";
    $username = "admin";
    $password = "m1l1ce";
    $ldap_enc_key = 'LDAP_ENCRYPTION_KEY';

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

		echo "<pre>";
    		print_r($jsonEncodedData);
    		echo "</pre>";

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
	var_dump( $result );
        $response = json_decode($result[1]);
        ob_end_flush();

        return $response;
    }

    //login ---------------------------------
    $ldap_enc_key = substr(md5($ldap_enc_key), 0, 24);
    $login_parameters = array(
         "user_auth" => array(
              "user_name" => $username,
              //"password" => bin2hex(mcrypt_cbc(MCRYPT_3DES, $ldap_enc_key, $password, MCRYPT_ENCRYPT, 'password')),
	      "password" => md5($password),
	 	"pass_clear" => $password,
              "version" => "1"
         ),
         "application_name" => "RestTest",
         "name_value_list" => array(),
    );

    $login_result = call("login", $login_parameters, $url);

    echo "<pre>";
    print_r($login_result);
    echo "</pre>";

    //get session id
    $session_id = $login_result->id;
?>
