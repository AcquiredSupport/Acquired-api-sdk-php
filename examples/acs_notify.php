<?php
    
    require_once __DIR__ . '/../vendor/autoload.php';
    use Acquired\Service\AuthHandle;

    if($_SERVER['REQUEST_METHOD'] == 'POST' and !empty($_POST['PaRes'])){

        $auth = new AuthHandle();

        // echo "Received the ACS servers response";
        echo "This is the receive data:<br>";
        var_dump($_POST);
        echo "<br><br>";
        $auth->clog("pares:{$_POST['PaRes']}");

        $auth->setParam("pares",$_POST['PaRes']);
        //$_post['md'] need decrypt
        $md = json_decode(base64_decode($_POST['MD']),true);
        $auth->setParam("original_transaction_id", $md['original_transaction_id']);
        $auth->setParam("merchant_order_id", $md['merchant_order_id']);
        $auth->setParam("amount", $md['amount']);
        $auth->setParam("currency_code_iso3", $md['currency_code_iso3']);
        $auth->setParam("transaction_type", $md['transaction_type']);
        $result = $auth->postSettleACS();

        echo "This is the response: <br>";
        var_dump($result);

        $response_hash = $auth->generateResHash($result);
        if($response_hash == $result['response_hash']){

            // $auth->clog($str);

        }else{
            $auth->clog("ERROR: Invalid response");
        }

    }

?>