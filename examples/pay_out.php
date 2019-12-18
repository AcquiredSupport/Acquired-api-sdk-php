<?php
    require_once __DIR__ . '/../vendor/autoload.php';
    use Acquired\Service\PayOutHandle;

	if($_SERVER['REQUEST_METHOD'] == 'POST'){
        
        /**
         * 'timestamp' has been set on HandlePub.php
         * 'transaction_type' has been set on HandlePub.php
         * 'request_hash' has been generate on HandlePub.php
         * 'company_id' has been set on Acquired.Config.php
         * 'company_pass' has been set on Acquired.Config.php
         *
         * step 1: Check customer post data.  (merchant action required)
         * step 2: Set parameters by use setParam().
         * step 3: Post parameters by use postJson().
         * step 4: Check response hash by use generateResHash().
         * step 5: Perform actions based on the result (merchant action required)
         * 
         */
        
        /*====== step 1: Check customer post data ======*/
        //just for example
        
        $merchant_order_id = date('Ymdhis').rand(10000,99999);//just for example

        /*====== step 2: Set parameters ======*/
        $aqpay = new PayOutHandle();
        //set transaction data
        $aqpay->setParam("merchant_order_id",$merchant_order_id);
        $aqpay->setParam("original_transaction_id",$_POST["original_transaction_id"]);
        $aqpay->setParam("amount",$_POST["amount"]);
        $aqpay->setParam("reference",$_POST["reference"]);

        /*====== step 3: Post parameters ======*/        
        $result = $aqpay->postJson();
        echo "response:<br>";
        echo "timestamp: ".$result['timestamp']."<br>";
        echo "response_code: ".$result['response_code']."<br>";
        echo "response_message: ".$result['response_message']."<br>";
        if(isset($result['transaction_type'])){
            echo "transaction_type: ".$result['transaction_type']."<br>";
        }
        if(isset($result['transaction_id'])){
            echo "transaction_id: ".$result['transaction_id']."<br>";    
        }
        
        /*====== step 4: Check response hash ======*/
        $response_hash = $aqpay->generateResHash($result);
        if($response_hash == $result['response_hash']){

            /*====== step 5: Perform actions based on the result ======*/
            echo "SUCCESS";

        }else{
            echo "ERROR: Invalid response hash";
        }
    }

?>