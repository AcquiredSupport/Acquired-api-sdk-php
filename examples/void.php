<?php
	
	require_once('../lib/Acquired.Helper.php');
    use Acquired\VoidPub;

    $void = new VoidPub();
    //set transaction data
    $original_transaction_id = (int)$_POST['original_transaction_id'];
    $void->setParam("original_transaction_id",$original_transaction_id);

    //response result
    $result = $void->postJson();
    echo "response:<br>";
    echo "timestamp: ".$result['timestamp']."<br>";
    echo "response_code: ".$result['response_code']."<br>";
    echo "response_message: ".$result['response_message']."<br>";
    echo "transaction_id: ".$result['transaction_id']."<br>";
    
    //perform actions based on the result
    $response_hash = $void->generateResHash($result);
    if($response_hash == $result['response_hash']){
    	echo "SUCCESS";
    }else{
    	echo "ERROR: Invalid response hash";	
    }

?>