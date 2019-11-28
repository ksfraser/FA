<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once'libraries/csrf-magic/csrf-magic.php';
$GLOBALS['csrf']['rewrite-js'] = 'libraries/csrf-magic/csrf-magic.js';

function requestValidateReadAccess(){
    if(isset($_SERVER['HTTP_REFERER'])){
        global $Authenticate_Path;
        if(stripos($_SERVER['HTTP_REFERER'],$Authenticate_Path)!==0){
            echo 'Illegal request';die;
        }
    }
    return true;
}

function requestValidateWriteAccess(){
    if($_SERVER['REQUEST_METHOD']!='POST'){ echo'Invalid request'; die;}
    requestValidateReadAccess();
    if(!csrf_check(false)){echo'Unsupported request';die;}
    return true;
}
