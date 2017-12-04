<?php
	if($_SERVER['REQUEST_METHOD'] == 'POST'){
        require_once('../lib/Acquired.Helper.php');

        /**
         * 'timestamp' has been set on Acquired.Helper.php
         * 'request_hash' has been set on Acquired.Helper.php
         * 'company_id' has been set on Acquired.Config.php
         * 'company_pass' has been set on Acquired.Config.php
         *
         * step 1: Check customer post data.  (need you to do)
         * step 2: Set parameters by use setParam().
         * step 3: Post parameters by use postJson().
         * step 4: Check response hash by use generateResHash().
         * step 5: Do your business according to the result. (need you to do)
         * 
         */
        
        /*====== step 1: Check customer post data ======*/
        //just eg
        $amount = (int)$_POST['amount'];
        $currency_code_iso3 = $_POST['currency'];
        $merchant_order_id = date('Ymdhis').rand(10000,99999);//just for example
        $transaction_type = $_POST['transaction_type'];
        $original_transaction_id = $_POST['transaction_id'];
        switch ($transaction_type) {
            case '2':$transaction_type = 'AUTH_CAPTURE';break;
            default: $transaction_type = 'AUTH_ONLY';break;
        }

        /*====== step 2: Set parameters ======*/
        $auth = new Auth_pub();
        $auth->setParam("transaction_type",$transaction_type);
        $auth->setParam("mid_id","1000");
        $auth->setParam("mid_pass","test");
        //set transaction data
        $auth->setParam("merchant_order_id",$merchant_order_id);
        $auth->setParam("amount",$amount);
        $auth->setParam("currency_code_iso3",$currency_code_iso3);
        $auth->setParam("original_transaction_id",$original_transaction_id);

        /*====== step 3: Post parameters ======*/
        $result = $auth->rebillJson();
        // var_dump($result);
        
        /*====== step 4: Check response hash ======*/
        $reponse_hash = $auth->generateResHash($result);
        if($reponse_hash == $result['response_hash']){

            /*====== step 5: Do your business ======*/
            echo "timestamp: ".$result['timestamp']."<br>";
            echo "response_code: ".$result['response_code']."<br>";
            echo "response_message: ".$result['response_message']."<br>";
            echo "transaction_id: ".$result['transaction_id']."<br>";
            echo "merchant_order_id: ".$result['merchant_order_id']."<br>";
            echo "amount: ".$result['amount']."&nbsp;".$result['currency_code_iso3']."<br>";

        }else{
            echo "ERROR: Invalid response";
        }
    }

?>