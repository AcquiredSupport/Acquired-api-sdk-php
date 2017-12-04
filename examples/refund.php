<?php
	
	require_once('../lib/Acquired.Helper.php');

    $refund = new Refund_pub();
    $refund->setParam("mid_id","1000");
    $refund->setParam("mid_pass","test");
    //set transaction data
    $original_transaction_id = $_POST['transaction_id'];
    $amount = $_POST['amount'];
    $refund->setParam("original_transaction_id",$original_transaction_id);
    $refund->setParam("amount",$amount);

    //response result
    $result = $refund->postJson();
    // var_dump($result);
    
    //mechant do someing
    $reponse_hash = $refund->generateResHash($result);
    if($reponse_hash == $result['response_hash']){
    	echo "timestamp: ".$result['timestamp']."<br>";
    	echo "response_code: ".$result['response_code']."<br>";
    	echo "response_message: ".$result['response_message']."<br>";
    	echo "transaction_id: ".$result['transaction_id']."<br>";
    }else{
    	echo "ERROR: Invalid response";	
    }

?>