<?php
	
	require_once('../lib/Acquired.Helper.php');
    use Acquired\CapturePub;

    $capture = new CapturePub();
    //set transaction data
    $original_transaction_id = $_POST['original_transaction_id'];
    $amount = $_POST['amount'];
    $capture->setParam("original_transaction_id",$original_transaction_id);
    $capture->setParam("amount",$amount);

    //response result
    $result = $capture->postJson();
    echo "response:<br>";
    echo "timestamp: ".$result['timestamp']."<br>";
    echo "response_code: ".$result['response_code']."<br>";
    echo "response_message: ".$result['response_message']."<br>";
    echo "transaction_id: ".$result['transaction_id']."<br>";
    
    //Perform actions based on the result
    $response_hash = $capture->generateResHash($result);
    if($response_hash == $result['response_hash']){
    	echo "SUCCESS";
    }else{
    	echo "ERROR: Invalid response hash";
    }

?>