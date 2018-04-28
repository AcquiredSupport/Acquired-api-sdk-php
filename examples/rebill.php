<?php
    require_once __DIR__ . '/../vendor/autoload.php';
    use Acquired\Service\AuthHandle;

	if($_SERVER['REQUEST_METHOD'] == 'POST'){        

        /**
         * 'timestamp' has been set on Acquired.Helper.php
         * 'request_hash' has been set on Acquired.Helper.php
         * 'company_id' has been set on Acquired.Config.php
         * 'company_pass' has been set on Acquired.Config.php
         *
         * step 1: Check customer post data. (merchant action required)
         * step 2: Set parameters by use setParam().
         * step 3: Post parameters by use postJson().
         * step 4: Check response hash by use generateResHash().
         * step 5: Perform actions based on the result (merchant action required)
         * 
         */
        
        /*====== step 1: Check customer post data ======*/
        //just eg
        $amount = (int)$_POST['amount'];
        $currency_code_iso3 = $_POST['currency'];
        $merchant_order_id = date('Ymdhis').rand(10000,99999);//just for example
        $transaction_type = $_POST['transaction_type'];
        $original_transaction_id = $_POST['original_transaction_id'];
        switch ($transaction_type) {
            case '2':$transaction_type = 'AUTH_CAPTURE';break;
            default: $transaction_type = 'AUTH_ONLY';break;
        }

        /*====== step 2: Set parameters ======*/
        $auth = new AuthHandle();
        $auth->setParam("transaction_type",$transaction_type);
        //set transaction data
        $auth->setParam("merchant_order_id",$merchant_order_id);
        $auth->setParam("amount",$amount);
        $auth->setParam("currency_code_iso3",$currency_code_iso3);
        $auth->setParam("original_transaction_id",$original_transaction_id);

        /*====== step 3: Post parameters ======*/
        $result = $auth->rebillJson();
        echo "response:<br>";
        echo "timestamp: ".$result['timestamp']."<br>";
        echo "response_code: ".$result['response_code']."<br>";
        echo "response_message: ".$result['response_message']."<br>";
        echo "transaction_id: ".$result['transaction_id']."<br>";
        echo "merchant_order_id: ".$result['merchant_order_id']."<br>";
        echo "amount: ".$result['amount']."&nbsp;".$result['currency_code_iso3']."<br>";
        
        /*====== step 4: Check response hash ======*/
        $response_hash = $auth->generateResHash($result);
        if($response_hash == $result['response_hash']){

            /*====== step 5: Perform actions based on the result ======*/
            echo "SUCCESS";

        }else{
            echo "ERROR: Invalid response hash";
        }
    }

?>