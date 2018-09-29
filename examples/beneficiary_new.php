<?php
    require_once __DIR__ . '/../vendor/autoload.php';
    use Acquired\Service\BeneficiaryNewHandle;

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
        $aqpay = new BeneficiaryNewHandle();
        //set transaction data
        $aqpay->setParam("merchant_order_id",$merchant_order_id);
        $aqpay->setParam("original_transaction_id",$_POST['original_transaction_id']);
        //set customer data
        $aqpay->setParam("customer_fname",$_POST['fname']);
        $aqpay->setParam("customer_lname",$_POST['lname']);
        //set billing data
        $aqpay->setParam("billing_street",$_POST['address']);
        $aqpay->setParam("billing_street2",$_POST['address2']);
        $aqpay->setParam("billing_city",$_POST['city']);
        $aqpay->setParam("billing_state",$_POST['state']);
        $aqpay->setParam("billing_zipcode",$_POST['zipcode']);
        $aqpay->setParam("billing_country_code_iso2",$_POST['iso2']);
        $aqpay->setParam("billing_phone",$_POST['phone']);
        $aqpay->setParam("billing_email",$_POST['email']);
        //set account data
        $aqpay->setParam("sort_code",$_POST['sort_code']);
        $aqpay->setParam("account_number",$_POST['account_number']);

        /*====== step 3: Post parameters ======*/        
        $result = $aqpay->postJson();
        echo "response:<br>";
        echo "timestamp: ".$result['timestamp']."<br>";
        echo "response_code: ".$result['response_code']."<br>";
        echo "response_message: ".$result['response_message']."<br>";
        if(isset($result['beneficiary_id'])){
            echo "beneficiary_id: ".$result['beneficiary_id']."<br>";    
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