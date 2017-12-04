<?php
	
	require_once('../lib/Acquired.Helper.php');

    $capture = new Capture_pub();
    $capture->setParam("mid_id","1000");
    $capture->setParam("mid_pass","test");
    //set transaction data
    $original_transaction_id = $_POST['transaction_id'];
    $amount = $_POST['amount'];
    $capture->setParam("original_transaction_id",$original_transaction_id);
    $capture->setParam("amount",$amount);

    //response result
    $result = $capture->postJson();
    // var_dump($result);
    
    //mechant do someing
    $reponse_hash = $capture->generateResHash($result);
    if($reponse_hash == $result['response_hash']){
    	echo "timestamp: ".$result['timestamp']."<br>";
    	echo "response_code: ".$result['response_code']."<br>";
    	echo "response_message: ".$result['response_message']."<br>";
    	echo "transaction_id: ".$result['transaction_id']."<br>";
    }else{
    	echo "ERROR: Invalid response";	
    }

?>