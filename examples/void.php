<?php
	
	require_once('../lib/Acquired.Helper.php');

    $void = new Void_pub();
    $void->setParam("mid_id","10000");
    $void->setParam("mid_pass","test");
    //set transaction data
    $original_transaction_id = (int)$_POST['transaction_id'];
    $void->setParam("original_transaction_id",$original_transaction_id);

    //response result
    $result = $void->postJson();
    // var_dump($result);
    
    //mechant do someing
    $reponse_hash = $void->generateResHash($result);
    if($reponse_hash == $result['response_hash']){
    	echo "timestamp: ".$result['timestamp']."<br>";
    	echo "response_code: ".$result['response_code']."<br>";
    	echo "response_message: ".$result['response_message']."<br>";
    	echo "transaction_id: ".$result['transaction_id']."<br>";
    }else{
    	echo "ERROR: Invalid response";	
    }

?>