<?php
require_once('../lib/Acquired.Helper.php');
use Acquired\AuthPub;

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    
    /**
     * 'timestamp' has been set in Acquired.Helper.php
     * 'request_hash' has been set in Acquired.Helper.php
     * 'company_id' has been set in Acquired.Config.php
     * 'company_pass' has been set in Acquired.Config.php
     * 'company_mid_id' has been set in Acquired.Config.php 
     *
     * step 1: Check customer post data. (merchant action required)
     * step 2: Set parameters by using setParam()
     * step 3: Post parameters by using postJson()
     * step 4: Check response hash by using generateResHash()
     * step 5: Perform actions based on the result (merchant action required)
     * 
     * Use 3-D secure
     * 1. setParam['action'] = "ENQUIRE"
     * 2. post this to Acquired with AUTH_ONLY or AUTH_CAPTURE request
     * 2. set pareq,ACS_url,termurl,md following Acquired response
     * 3. post to ACS by using postToACS()
     * 4. The termurl will receive the ACS post. Please see acs_notify.php
     * 5. post SETTLEMENT request to Acquired via acs_notify.php
     * 
     */
    
    /*====== step 1: Check customer post data ======*/        
    //just for example
    $amount = (int)$_POST['amount'];
    $merchant_order_id = date('Ymdhis').rand(10000,99999);//just for example
    $transaction_type = $_POST['transaction_type'];        
    switch ($transaction_type) {
        case '2':$transaction_type = 'AUTH_CAPTURE';break;
        default: $transaction_type = 'AUTH_ONLY';break;
    }
    $subscription_type = $_POST['subscription'];
    switch ($subscription_type) {
        case '1':$subscription_type = 'INIT';break;
        default: $subscription_type = '';break;
    }
    $tds_action = $_POST['tds'];
    switch ($tds_action) {
        case '1':$tds_action = 'ENQUIRE';break;
        default: $tds_action = '';break;
    }        

    /*====== step 2: Set parameters ======*/
    $auth = new AuthPub();

    $auth->setParam("vt","");
    $auth->setParam("useragent","");
    //set transaction data        
    $auth->setParam("merchant_order_id",$merchant_order_id);
    $auth->setParam("transaction_type",$transaction_type);
    $auth->setParam("subscription_type",$subscription_type);
    $auth->setParam("amount",$amount);
    $auth->setParam("currency_code_iso3",$_POST['currency']);
    $auth->setParam("merchant_customer_id","b10001");
    $auth->setParam("merchant_custom_1","C1");
    $auth->setParam("merchant_custom_2","C2");
    $auth->setParam("merchant_custom_3","C3");
    //set customer data
    $auth->setParam("customer_title",$_POST['title']);
    $auth->setParam("customer_fname",$_POST['fname']);
    $auth->setParam("customer_mname",$_POST['mname']);
    $auth->setParam("customer_lname",$_POST['lname']);
    $auth->setParam("customer_gender",$_POST['gender']);
    $auth->setParam("customer_dob",$_POST['dob']);
    $auth->setParam("customer_ipaddress",$auth->clientIp());
    $auth->setParam("customer_company",$_POST['company']);
    //set billing data
    $auth->setParam("cardholder_name",$_POST['name']);
    $auth->setParam("cardnumber",$_POST['number']);
    $auth->setParam("card_type",$_POST['type']);
    $auth->setParam("cardcvv",$_POST['cvv']);
    $auth->setParam("cardexp",$_POST['cardexp']);
    $auth->setParam("billing_street",$_POST['address']);
    $auth->setParam("billing_street2",$_POST['address2']);
    $auth->setParam("billing_city",$_POST['city']);
    $auth->setParam("billing_state",$_POST['state']);
    $auth->setParam("billing_zipcode",$_POST['zipcode']);
    $auth->setParam("billing_country_code_iso2",$_POST['iso2']);
    $auth->setParam("billing_phone",$_POST['phone']);
    $auth->setParam("billing_email",$_POST['email']);
    //set tds
    $auth->setParam("action",$tds_action);

    /*====== step 3: Post parameters ======*/
    $result = $auth->postJson();
    echo "response:<br>";
    echo "company_id: ".$result['company_id']."<br>";
    echo "mid: ".$result['mid']."<br>";
    echo "timestamp: ".$result['timestamp']."<br>";
    echo "response_code: ".$result['response_code']."<br>";
    echo "response_message: ".$result['response_message']."<br>";
    echo "transaction_id: ".$result['transaction_id']."<br>";
    echo "merchant_order_id: ".$result['merchant_order_id']."<br>";
    echo "amount: ".$result['amount']."&nbsp;".$result['currency_code_iso3']."<br>";
    
    /*====== step 4: Check response hash ======*/
    $reponse_hash = $auth->generateResHash($result);
    if($reponse_hash == $result['response_hash']){

        /*====== step 5: Perform actions based on the result ======*/

        /*====== Deal 3-D secure ======*/
        if($tds_action == "ENQUIRE" and isset($result['tds'])){
            $tdsobj = $result['tds'];

            if(in_array($result['response_code'], array(501,502))){
                $auth->setParam('pareq',$tdsobj['pareq']);
                $auth->setParam('ACS_url',$tdsobj['url']);
                //where ACS send the outcome to you
                $termurldir = dirname('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
                $termurl = $termurldir."/acs_notify.php";
                $auth->setParam('termurl',$termurl);
                /**
                 *  set MD field 
                 *  This field will required for the subsequent SETTLEMENT request.   
                 *  if you store these param in your sqlserver then you can just set a id
                    And you can read these param from you sqlserver when post SETTLEMENT request.
                 */
                $md['company_id'] = $result['company_id'];
                $md['original_transaction_id'] = $result['transaction_id'];
                $md['merchant_order_id'] = $result['merchant_order_id'];
                $md['amount'] = $result['amount'];
                $md['currency_code_iso3'] = $result['currency_code_iso3'];
                $md['transaction_type'] = $result['transaction_type'];
                //$md must be encrypted and Base64 encoded.
                $md = base64_encode(json_encode($md));
                $auth->setParam('md',$md);
                echo "<!DOCTYPE html>
                <html>
                <head>
                    <title>3D Secure Redirect Page</title>
                    <script type=\"text/javascript\">
                        function OnLoadEvent() {
                            document.getElementById('acs_form').submit();
                        }
                    </script>
                </head>
                <body onload=\"OnLoadEvent()\">
                    <form id=\"acs_form\" action=\"{$tdsobj['url']}\" method=\"post\">
                        <input type=\"hidden\" name=\"PaReq\" value=\"{$tdsobj['pareq']}\" />
                        <input type=\"hidden\" name=\"TermUrl\" value=\"{$termurl}\" />
                        <input type=\"hidden\" name=\"MD\" value=\"{$md}\" />
                    </form>
                </body>
                </html>";
            }else{
                echo "ERROR: Unable to verify enrollement, decline transaction";
            }
        }

    }else{
        echo "ERROR: Invalid response hash";
    }
}

?>