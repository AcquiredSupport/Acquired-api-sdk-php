<?php
    
    session_start();
    require_once __DIR__ . '/../vendor/autoload.php';
    use Acquired\Service\AuthHandle;

    if($_SERVER['REQUEST_METHOD'] == 'POST'){

        $auth = new AuthHandle();

        if(isset($_POST['cres'])){
            $threeDSSessionData = json_decode(base64_decode($_POST["threeDSSessionData"]), true);

            $original_transaction_id = $threeDSSessionData["transaction_id"];
            $transaction_type = $threeDSSessionData["transaction_type"];
            $merchant_order_id = $threeDSSessionData["merchant_order_id"];

            $auth->setParam("cres",$_POST['cres']);
            $auth->setParam("original_transaction_id", $original_transaction_id);
            $auth->setParam("transaction_type", $transaction_type);
            $result = $auth->postSettleACSv2();

            $response_hash = $auth->generateResHash($result);
            if($response_hash == $result['response_hash']){

                var_dump($result);
                // to do

            }else{
                die("ERROR: Invalid response");
            }
        }else{
            die("Didn't receive cres".json_encode($_POST));
        }

    }

?>