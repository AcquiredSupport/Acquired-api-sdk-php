<?php

namespace Acquired\Service;
/**
 * 
 * HandlePub -- All class extends the public class
 * 		setParam -- set need parameters
 * 		setBasicParam -- set config parameters and generate request hash code
 * 		generateResHash  -- generate response hash code
 * 		createData -- create request array data
 * 		createJson -- change array to json
 * 		postJson -- curl api
 * 		
 */

use Acquired\AcquiredConfig;
use Acquired\AcquiredException;
use Acquired\AcquiredCommon;

/**
 * require basic class
 */
class HandlePub extends AcquiredCommon
{
	protected $param;
	protected $url;
	protected $curl_timeout;

	public function __construct(){
		$this->url = AcquiredConfig::REQUIREURL;
		$this->curl_timeout = AcquiredConfig::CURLTIMEOUT;
	}

	/**
	 * set parameter
	 */
	public function setParam($param,$paramVal){
		$this->param[$this->trimString($param)] = $paramVal;
	}

	public function clearParam(){
		$this->param = array();
	}

	/**
	 * set basic param and generate request_hash
	 */
	public function setBasicParam(){
		$this->param["company_id"] = AcquiredConfig::COMPANYID;
		$this->param["company_pass"] = AcquiredConfig::COMPANYPASS;
		$this->param["company_mid_id"] = AcquiredConfig::COMPANYMIDID;

		if(empty($this->param["company_id"])){
			throw new AcquiredException("ERROR: Require company_id");
		}
		if(empty($this->param["company_pass"])){
			throw new AcquiredException("ERROR: Require company_pass");
		}
		
		$this->param['timestamp'] = $this->now();
		$hashcode = AcquiredConfig::HASHCODE;
		$this->param["request_hash"] = $this->sha256hash($this->param,$hashcode);
	}

	/**
	 * generate response hash
	 */
	public function generateResHash($param){
		$hashcode = AcquiredConfig::HASHCODE;
		return $this->responseHash($param,$hashcode);
	}

	public function createData(){
		$this->setBasicParam();
		$data = array();
		foreach($this->param as $k=>$v){
			$data[$k] = $v;
		}
		return $data;
	}

	public function createJson(){
		$data = $this->createData();
		return json_encode($data);
	}

	public function postJson(){
		$json = $this->createJson();
		$response = $this->httpsRequest($this->url,$json,$this->curl_timeout,"json");
		$this->clearParam();
		return json_decode($response,true);
	}
	
}