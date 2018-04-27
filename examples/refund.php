<?php
	
	require_once('../lib/Acquired.Helper.php');
    use Acquired\RefundPub;

    $refund = new RefundPub();
    //set transaction data
    $original_transaction_id = $_POST['original_transaction_id'];
    $amount = $_POST['amount'];
    $refund->setParam("original_transaction_id",$original_transaction_id);
    $refund->setParam("amount",$amount);

    //response result
    $result = $refund->postJson();
    echo "response:<br>";
    echo "timestamp: ".$result['timestamp']."<br>";
    echo "response_code: ".$result['response_code']."<br>";
    echo "response_message: ".$result['response_message']."<br>";
    echo "transaction_id: ".$result['transaction_id']."<br>";
    
    //perform actions based on the result
    $response_hash = $refund->generateResHash($result);
    if($response_hash == $result['response_hash']){
    	echo "SUCCESS";
    }else{
    	echo "ERROR: Invalid response";	
    }

?>