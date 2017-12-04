<?php

require_once "Acquired.Config.php";

class AcquiredCommon
{	

	function trimString($value){
		$ret = null;
		if (null != $value) 
		{
			$ret = $value;
			if (strlen($ret) == 0) 
			{
				$ret = null;
			}
		}
		return $ret;
	}

	function now(){
		return date("YmdHis");
	}

	//require hash
	function sha256hash($param,$secret){
		if(isset($param['company_id'])){
			$_id = $param['company_id'];
		}else{
			$_id = $param['mid_id'];
		}
	    if(in_array($param['transaction_type'],array('AUTH_ONLY','AUTH_CAPTURE','CREDIT'))){
			$str=$param['timestamp'].$param['transaction_type'].$_id.$param['merchant_order_id'];
		}elseif(in_array($param['transaction_type'],array('CAPTURE','VOID','REFUND','SUBSCRIPTION_MANAGE'))){
			$str=$param['timestamp'].$param['transaction_type'].$_id.$param['original_transaction_id'];
		}
		return hash('sha256',$str.$secret);
	}

	//response hash
	function responseHash($param,$secret){
		if(isset($param['company_id'])){
			$_id = $param['company_id'];
		}else{
			$_id = $param['mid'];
		}
		$str=$param['timestamp'].$param['transaction_type'].$_id.$param['transaction_id'].$param['response_code'];
		return hash('sha256',$str.$secret);	
	}

	function client_ip(){
		if (isset($_SERVER['HTTP_CLIENT_IP']) && strcasecmp($_SERVER['HTTP_CLIENT_IP'], "unknown")){
			$ip = $_SERVER['HTTP_CLIENT_IP'];
	    }else if (isset($_SERVER['HTTP_CLIENTIP']) && strcasecmp($_SERVER['HTTP_CLIENTIP'], "unknown")){
			$ip = $_SERVER['HTTP_CLIENTIP'];
	    }else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], "unknown")){
			$ips = explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
			$ip = $ips[0];
	    }else if (isset($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")){
			$ip = $_SERVER['REMOTE_ADDR'];
	    }else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")){
			$ip = $_SERVER['REMOTE_ADDR'];
	    }else{
			$ip = "unknown";
	    }
		return($ip);
	}

	function xml_to_json($xml){
	    $xmlParser = xml_parser_create();
	    $info = xml_parse($xmlParser, $xml);
	    xml_parser_free($xmlParser);

	    if(empty($xml) || $info == 0){
	        $this->_unauthorized();
	    }

	    $xml = simplexml_load_string($xml);
	    return json_encode(to_array($xml));
	}

	function to_array($object){
	    if(!empty($object)){
	        $arr = @(array)$object;
	        foreach ($arr as &$value) {
	            if(is_object($value)){
	                $value = to_array($value);
	            }
	        }
	        return $arr;    
	    }else{
	        return '';
	    }
	}

	function array_to_xml($arr){
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
        	 if (is_numeric($val))
        	 {
        	 	$xml.="<".$key.">".$val."</".$key.">"; 

        	 }
        	 else
        	 	$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";  
        }
        $xml.="</xml>";
        return $xml; 
    }

    function clog($msg){
    	$logpath = AcquiredConfig::LOGPATH;
    	if(!is_dir($logpath)){
            mkdir($logpath, 0775, true);
        }

    	$log_path = $logpath.date('Ymd').'.log';
        
        $time = date('Y-m-d H:i:s');
        $message = $time.'----'.$msg."\n";
        $is_ok = file_put_contents($log_path, $message, FILE_APPEND);

        if(!$is_ok){
            die("Not able to write log. file: '{$log_path}', message: '{$message}'.");
        }
    }

	public function https_request($url,$data=null,$second=30,$type=null){
       	$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        switch(strtolower($type)){
        	case "json" :{
        		$header[] = "Content-type: application/json";
        		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        	}break;
        	case "xml" :{
        		$header[] = "Content-type: text/xml";
        		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        	}break;
        	default:{
        		curl_setopt($ch, CURLOPT_HEADER, FALSE);
        	}break;
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if(!empty($data)){
        	curl_setopt($ch, CURLOPT_POST, TRUE);
        	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);	
        }
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        $result = curl_exec($ch);
		if(!$result){
			$errno = curl_errno($ch);
			$errmes = curl_error($ch);
			echo "curl_errno:{$errno}, curl_errmes:{$errmes}\n";
		}
		curl_close($ch);
		return $result;
	}

}