<?php

define("SQLJ_STATUS_BAD_WORD", 0);
define("SQLJ_STATUS_ADMISSION_WORD", 1);
define("SQLJ_STATUS_CORRECT_ANSWER", 2);
define("SQLJ_STATUS_WRONG_ANSWER", 3);
define("SQLJ_STATUS_TIME_LIMIT_EXCEEDED", 4);
define("SQLJ_STATUS_MEMORY_LIMIT_EXCEEDED", 5);
define("SQLJ_STATUS_UNKNOWN_ERROR", 6);
define("SQLJ_STATUS_NOT_CHECKED", 7);
define("SQLJ_STATUS_CHECKED", 8);

function sqljudge_get_supported_dbms_list() {
    $get_path = '/api/dbms';
    $dbms_list = get_data($get_path);

    if ($dbms_list === null) {
        echo get_string('error', 'local_sqljudge') . get_string('parserespfail', 'local_sqljudge');
        return false;
    }

    $result = array();
    
    for ($i = 0; $i<count($dbms_list); $i++) {
        $result[$dbms_list[$i]["name"]] = $dbms_list[$i]["name"];
    }

    return $result;
}

function get_databases() {
    $get_path = '/api/databases';

    $databases = get_data($get_path);
    // test database
    if (!empty($databases)) {
        $options = array();
        for ($i=0; $i<count($databases); $i++){
            $options[$databases[$i]["id"]] = $databases[$i]["dbmsName"] . ': ' . $databases[$i]["fileName"] . ' (' . $databases[$i]["description"] . ')';
        }
        return $options;
    }
    return null;
}

function post_data($json_data, $post_path) {
    $backendAddress = get_config('local_sqljudge', 'backendaddress');
    $backendPort = explode(':', $backendAddress)[1];

    $headers = array(
        "Accept: application/json",
        "Content-Type: application/json"
    );

    //POST data to server
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $backendAddress . $post_path,
        CURLOPT_PORT => $backendPort,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $json_data,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_FOLLOWLOCATION => true,
    ));
    
    //for debug only
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    return $httpCode;
}

function get_data($get_path) {
    $backendAddress = get_config('local_sqljudge', 'backendaddress');
    $backendPort = explode(':', $backendAddress)[1];
    
    $headers = array(
        "Accept: application/json",
        "Content-Type: application/json"
    );

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_HEADER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_AUTOREFERER => true,
        CURLOPT_URL => $backendAddress . $get_path,
        CURLOPT_PORT => $backendPort,
        CURLOPT_HTTPHEADER => $headers,
    ));
            
    //for debug only
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $resp = curl_exec($curl);

    if ($resp === false) {
        // Error occurred during the request
        $error = curl_error($curl);
        curl_close($curl);
        echo "Error: " . $error;
        exit();
    }
    curl_close($curl);
    // answer
    $answer = json_decode($resp, true);
    return $answer;
}

function delete_data($delete_path) {
    $backendAddress = get_config('local_sqljudge', 'backendaddress');
    $backendPort = explode(':', $backendAddress)[1];
    
    $headers = array(
        "Accept: application/json",
        "Content-Type: application/json"
    );

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_CUSTOMREQUEST => "DELETE",
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_URL => $backendAddress . $delete_path,
        CURLOPT_PORT => $backendPort,
        CURLOPT_HEADER => true,
        CURLOPT_NOBODY => true,
        CURLOPT_HTTPHEADER => $headers,
    ));
            
    //for debug only
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $resp = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    return $httpCode;
}
