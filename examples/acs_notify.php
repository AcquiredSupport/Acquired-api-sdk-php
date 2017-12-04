<?php
	
	require_once('../lib/Acquired.Helper.php');

    if($_SERVER['REQUEST_METHOD'] == 'POST' and !empty($_POST['PaRes'])){

        $auth = new Auth_pub();

        $auth->clog("Received the ACS servers response");

        $auth->setParam("pares",$_POST['PaRes']);
        //$_post['md'] need decrypt
        $md = json_decode(base64_decode($_POST['md']),true);
        $auth->setParam("mid_id", $md['mid_id']);
        $auth->setParam("mid_pass", $md['mid_pass']);
        $auth->setParam("original_transaction_id", $md['transaction_id']);
        $auth->setParam("merchant_order_id", $md['merchant_order_id']);
        $auth->setParam("amount", $md['amount']);
        $auth->setParam("currency_code_iso3", $md['currency_code_iso3']);
        $auth->setParam("transaction_type", $md['transaction_type']);
        $result = $auth->postSettleACS();

        $reponse_hash = $auth->generateResHash($result);
        if($reponse_hash == $result['response_hash']){

            $str = "transaction_id:".$result['transaction_id']
            ."\nmerchant_order_id:".$result['merchant_order_id']
            ."\nbank_response_code:".$result['bank_response_code']
            ."\nresponse_code: ".$result['response_code']
            ."\nresponse_message: ".$result['response_message']
            ."\ntds-status:".$result['tds']['status']
            ."\ntds-eci:".$result['tds']['eci'];
            $auth->clog($str);

        }else{
            $auth->clog("ERROR: Invalid response");
        }

    }

?>