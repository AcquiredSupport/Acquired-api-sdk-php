<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Acquired\Service\AuthHandle;

session_start();

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
     * Use 3-D secure v1
     * 1. setParam['action'] = "ENQUIRE"
     * 2. post this to Acquired with AUTH_ONLY or AUTH_CAPTURE request
     * 2. set pareq,ACS_url,termurl,md following Acquired response
     * 3. post to ACS
     * 4. The termurl will receive the ACS post. Please see acs_notify.php
     * 5. post SETTLEMENT request to Acquired via acs_notify.php
     *
     * Use 3-D secure v2
     * 1. setParam['action'] = "SCA"
     * 2. $auth->verifyCard()
     * 3. request method url
     * 4. $auth->postJson
     * 5. if is challenge: request ACS service and $auth->postSettleACSv2()(via td2_challenge.php)
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
        case '2':$tds_action = 'SCA';break;
        default: $tds_action = '';break;
    }        

    /*====== step 2: Set parameters ======*/
    $auth = new AuthHandle();

    $auth->setParam("vt","");
    $auth->setParam("useragent","");
    //set transaction data        
    $auth->setParam("merchant_order_id",$merchant_order_id);
    $auth->setParam("transaction_type",$transaction_type);
    $auth->setParam("subscription_type",$subscription_type);
    $auth->setParam("amount",$amount);
    $auth->setParam("currency_code_iso3",$_POST['currency']);
    $auth->setParam("merchant_customer_id","b10001");
    $auth->setParam("merchant_custom_1","");
    $auth->setParam("merchant_custom_2","");
    $auth->setParam("merchant_custom_3","");
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
    $auth->setParam("action", $tds_action);
    //set link
    $auth->setParam("beneficiary", $_POST['beneficiary']);

    /*====== step 3: Post parameters ======*/

    // [3-DS v2] vefify whether the card support 3-DS v2
    if($tds_action == "SCA"){
        
        //Where the ACS should POST back their response to if threeDSMethodData is returned.
        $localhost = dirname('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        $method_notification_url = $localhost."/tds2_method_notification.php";
        $auth->setParam("method_notification_url", $method_notification_url);

        $result = $auth->verifyCard();
        
        if(!empty($result["enrolled"]) && !empty($result["server_trans_id"])){            
            $method_url = $result["method_url"];
            $threeDSMethodData = $result["threeDSMethodData"];

            echo "<!DOCTYPE html>
                <html>
                <meta charset=\"ISO-8859-1\">
                <head>
                <title>Sample Open method_url Page</title>
                <script type=\"text/javascript\">
                    function OnLoadEvent() {
                        document.getElementById('acs_form').submit();
                    }
                </script>
                </head>
                <body onload=\"OnLoadEvent()\">
                    <iframe id=\"hidden_iframe\" name=\"hidden_iframe\" style=\"display: none;\"></iframe>
                    <form method=\"POST\" id=\"acs_form\" action=\"{$method_url}\" target=\"hidden_iframe\">
                        <input type=\"hidden\" name=\"threeDSMethodData\" value=\"{$threeDSMethodData}\">
                    </form>
                </body>
                </html>";

                /**
                 * If the issuing bank then successfully gathers the Device ID they will send back a POST message to your method_notification_url within 10 seconds (please be aware, the timeframe should be a lot quicker this is the longest it can take) returning the threeDSMethodData.
                 */
                sleep(3);

                // $_SESSION set in merthod_notification.php
                if(isset($_SESSION["method_url_completion"])){
                    $method_url_completion = $_SESSION["method_url_completion"];
                }else{
                    $method_url_completion = 3;
                }

                $browser_data = json_decode($_POST["browser_data"], true);

                //browser_data
                $auth->setParam("accept_header", $_SERVER['HTTP_ACCEPT']);
                $auth->setParam("color_depth", $auth->getTdsColorDepthType($browser_data['colorDepth']));
                $auth->setParam("ip", "127.0.0.1");
                $auth->setParam("java_enabled", "true");
                $auth->setParam("javascript_enabled", "true");
                $auth->setParam("challenge_window_size", "WINDOWED_600X400");
                $auth->setParam("language", $browser_data['browserLanguage']);
                $auth->setParam("screen_height", $browser_data['screenHeight']);
                $auth->setParam("screen_width", $browser_data['screenWidth']);
                $auth->setParam("user_agent", $browser_data['userAgent']);
                $auth->setParam("timezone", $browser_data['browserTimezoneZoneOffset']);

                /**
                 * source value:
                 * 1 = Browser
                 * 2 = Mobile SDK
                 */
                $auth->setParam("source", 1);
                /**
                 * The type of transaction you are processing:
                 * 1 = Cardholder Verification (Zero Value Auth)
                 * 2 = Ad hoc payment (Customer Present)
                 * 3 = Recurring (Setting up a monthly payment for the same amount)
                 * 4 = Add Card (Just adding a new card on file for the customers account)
                 */
                $auth->setParam("type", 2);
                /**
                 * If you'd like the customer to be challenged:
                 * 0 = No Preference
                 * 1 = Don't Challenge
                 * 2 = Request Challenge
                 */
                $auth->setParam("preference", 0);
                $auth->setParam("server_trans_id", $result["server_trans_id"]);
                /**
                 * 1 = Yes, it was completed
                 * 2 = No we didn't get a response
                 * 3 = We never got any method_data
                 */
                $auth->setParam("method_url_complete", $method_url_completion);
                /**
                 * Link to the contact / customer care section of your website, just in case something goes wrong.
                 */
                $contact_url = $localhost."/tds2_contact.php";
                $auth->setParam("contact_url", $contact_url);

                /**
                 * In the event of a Challenge Flow, where the ACS should POST back their response to.
                 */
                $challenge_url = $localhost."/tds2_challenge.php";
                $auth->setParam("challenge_url", $challenge_url);

        }else{
            echo "verify card failed, the result is:".json_encode($result);
            exit;
        }
    }

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

        if(isset($result['tds'])){
            // Deal 3-D secure v1
            if($tds_action == "ENQUIRE"){
                if(in_array($result['response_code'], array(501,502))){
                    $tdsobj = $result['tds'];
                    $auth->setParam('pareq',$tdsobj['pareq']);
                    $auth->setParam('ACS_url',$tdsobj['url']);
                    //where ACS send the outcome to you
                    $termurldir = dirname('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
                    // http://dev.mamp.com/sdk/test-composer/examples
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

            // Deal 3-D secure v2
            if($tds_action == "SCA"){
                if(in_array($result['response_code'], array(503))){

                    $tdsobj = $result['tds'];
                    //The value will post back to challenge url
                    $threeDSSessionData["transaction_id"] = $result["transaction_id"];
                    $threeDSSessionData["transaction_type"] = $result["transaction_type"];
                    $threeDSSessionData["merchant_order_id"] = $result["merchant_order_id"];
                    $threeDSSessionData = base64_encode(json_encode($threeDSSessionData));

                    echo "<!DOCTYPE html>
                    <html>
                    <head>
                        <title>3D Secure Redirect Page</title>
                        <script type=\"text/javascript\">
                            function OnLoadEvent() {
                                document.getElementById('sca_acs_form').submit();
                            }
                        </script>
                    </head>
                    <body onload=\"OnLoadEvent()\">
                        <form id=\"sca_acs_form\" action=\"{$tdsobj['url']}\" method=\"post\">
                            <input type=\"hidden\" name=\"creq\" value=\"{$tdsobj['creq']}\" />
                            <input type=\"hidden\" name=\"threeDSSessionData\" value=\"{$threeDSSessionData}\" />
                        </form>
                    </body>
                    </html>";
                }
            }
        }

    }else{
        echo "ERROR: Invalid response hash";
    }
}

?>