<?php
        
    /**
     * Use to test 3-D secure process.
     * 'pareq','ACS_url' should be from Acquired response.
     * 'md' need to be encrypted.
     */

    require_once('../lib/Acquired.Helper.php');

	$auth = new Auth_pub();

    $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $termurldir = dirname($url);
    $termurl = $termurldir."/acs_notify.php";
    $auth->setParam('pareq','testpareq');//should be Acquired response
    $auth->setParam('ACS_url',$termurldir."/acs_servers.php");//should be Acquired response
    $auth->setParam('termurl',$termurl);
    
    $md['mid_id'] = '10000';
    $md['mid_pass'] = 'test';
    $md['transaction_id'] = '100000';
    $md['merchant_order_id'] = '20171128091112';
    $md['amount'] = 1;
    $md['currency_code_iso3'] = "CBA";
    $md['transaction_type'] = "AUTH_ONLY";
    $md = base64_encode(json_encode($md));//$md need encrypt
    $auth->setParam('md',$md);
    $postResult = $auth->postToACS();

    echo $postResult;

?>