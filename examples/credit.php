<?php
	if($_SERVER['REQUEST_METHOD'] == 'POST'){
        require_once('../lib/Acquired.Helper.php');    

        /**
         * 'timestamp' has been set on Acquired.Helper.php
         * 'transaction_type' has been set on Acquired.Helper.php
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
        //just for example
        $amount = (int)$_POST['amount'];
        $merchant_order_id = date('Ymdhis').rand(10000,99999);//just for example

        /*====== step 2: Set parameters ======*/
        $credit = new Credit_pub();
        $credit->setParam("mid_id","1000");
        $credit->setParam("mid_pass","test");
        //set transaction data        
        $credit->setParam("merchant_order_id",$merchant_order_id);
        $credit->setParam("amount",$amount);
        $credit->setParam("currency_code_iso3",$_POST['currency']);
        $credit->setParam("merchant_customer_id","C101");
        $credit->setParam("merchant_custom_1","C1");
        $credit->setParam("merchant_custom_2","C2");
        $credit->setParam("merchant_custom_3","C3");
        //set customer data
        $credit->setParam("customer_title",$_POST['title']);
        $credit->setParam("customer_fname",$_POST['fname']);
        $credit->setParam("customer_mname",$_POST['mname']);
        $credit->setParam("customer_lname",$_POST['lname']);
        $credit->setParam("customer_gender",$_POST['gender']);
        $credit->setParam("customer_dob",$_POST['dob']);
        $credit->setParam("customer_ipaddress",$credit->client_ip());
        $credit->setParam("customer_company",$_POST['company']);
        //set billing data
        $credit->setParam("cardholder_name",$_POST['name']);
        $credit->setParam("cardnumber",$_POST['number']);
        $credit->setParam("card_type",$_POST['type']);
        $credit->setParam("cardcvv",$_POST['cvv']);
        $credit->setParam("cardexp",$_POST['cardexp']);
        $credit->setParam("billing_street",$_POST['address']);
        $credit->setParam("billing_street2",$_POST['address2']);
        $credit->setParam("billing_city",$_POST['city']);
        $credit->setParam("billing_state",$_POST['state']);
        $credit->setParam("billing_zipcode",$_POST['zipcode']);
        $credit->setParam("billing_country_code_iso2",$_POST['iso2']);
        $credit->setParam("billing_phone",$_POST['phone']);
        $credit->setParam("billing_email",$_POST['email']);

        /*====== step 3: Post parameters ======*/        
        $result = $credit->postJson();
        // var_dump($result);
        
        /*====== step 4: Check response hash ======*/
        $reponse_hash = $credit->generateResHash($result);
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