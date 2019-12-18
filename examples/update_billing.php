<?php
    require_once __DIR__ . '/../vendor/autoload.php';
    use Acquired\Service\SubscriptionManageHandle;

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        
        /**
         * 'timestamp' has been set on Acquired.Helper.php
         * 'transaction_type' has been set on Acquired.Helper.php
         * 'subscription_type' has been set on Acquired.Helper.php
         * 'request_hash' has been generate on Acquired.Helper.php
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
        $sm = new SubscriptionManageHandle();
        //set transaction data
        $sm->setParam("merchant_order_id",$merchant_order_id);
        $sm->setParam("original_transaction_id",$_POST['original_transaction_id']);
        //set customer data
        $sm->setParam("customer_title",$_POST['title']);
        $sm->setParam("customer_fname",$_POST['fname']);
        $sm->setParam("customer_mname",$_POST['mname']);
        $sm->setParam("customer_lname",$_POST['lname']);
        $sm->setParam("customer_gender",$_POST['gender']);
        $sm->setParam("customer_dob",$_POST['dob']);
        $sm->setParam("customer_ipaddress",$sm->clientIp());
        $sm->setParam("customer_company",$_POST['company']);
        //set billing data
        $sm->setParam("cardholder_name",$_POST['name']);
        $sm->setParam("cardnumber",$_POST['number']);
        $sm->setParam("card_type",$_POST['type']);
        $sm->setParam("cardcvv",$_POST['cvv']);
        $sm->setParam("cardexp",$_POST['cardexp']);
        $sm->setParam("billing_street",$_POST['address']);
        $sm->setParam("billing_street2",$_POST['address2']);
        $sm->setParam("billing_city",$_POST['city']);
        $sm->setParam("billing_state",$_POST['state']);
        $sm->setParam("billing_zipcode",$_POST['zipcode']);
        $sm->setParam("billing_country_code_iso2",$_POST['iso2']);
        $sm->setParam("billing_phone",$_POST['phone']);
        $sm->setParam("billing_email",$_POST['email']);

        /*====== step 3: Post parameters ======*/        
        $result = $sm->postJson();
        echo "response:<br>";
        echo "timestamp: ".$result['timestamp']."<br>";
        echo "response_code: ".$result['response_code']."<br>";
        echo "response_message: ".$result['response_message']."<br>";
        echo "transaction_id: ".$result['transaction_id']."<br>";
        
        /*====== step 4: Check response hash ======*/
        $response_hash = $sm->generateResHash($result);
        if($response_hash == $result['response_hash']){

            /*====== step 5: Perform actions based on the result ======*/
            echo "SUCCESS";

        }else{
            echo "ERROR: Invalid response hash";
        }
    }

?>