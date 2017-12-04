<?php
/**
 * 
 * AcquiredPub -- All class extends the public class
 * 		setParam -- set need parameters
 * 		setBasicParam -- set config parameters and generate request hash code
 * 		generateResHash  -- generate response hash code
 * 		createData -- create request array data
 * 		createJson -- change array to json
 * 		postJson -- curl api
 * 	Auth_pub  -- deal AUTH_ONLY and AUTH_CAPTURE request
 * 		checkData -- verify the required data
 *		createData -- create request array data
 * 		createRebillData -- create rebill request array data
 *		rebillJson -- curl rebill api
 *		checkACSData -- check post to ACS Data
 *		postToACS -- post to ACS
 *		checkSettleACSData -- verify the request settlement data
 *		createSettlementACS -- create settlement request array data
 *		postSettleACS -- curl ACS settlement api
 *	Auth_only_pub
 *	Capture_pub
 *	Auth_capture_pub
 *  Void_pub
 * 	Refund_pub
 *	Credit_pub
 * 	Subscription_manage_pub --use to update billing
 * 
 */

require_once "Acquired.Common.php";
require_once "Acquired.Config.php";
require_once "Acquired.Exception.php";

/**
 * require basic class
 */
class AcquiredPub extends AcquiredCommon
{
	protected $param;
	protected $url;
	protected $curl_timeout;

	function __construct(){
		$this->url = AcquiredConfig::REQUIREURL;
		$this->curl_timeout = AcquiredConfig::CURLTIMEOUT;
	}

	/**
	 * set parameter
	 */
	function setParam($param,$paramVal){
		$this->param[$this->trimString($param)] = $paramVal;
	}

	function clearParam(){
		$this->param = array();
	}

	/**
	 * set basic param and generate request_hash
	 */
	function setBasicParam(){
		if(empty($this->param["mid_pass"])){
			$this->param["company_id"] = AcquiredConfig::COMPANYID;
			$this->param["company_pass"] = AcquiredConfig::COMMPANYPASS;
			if(isset($this->param["mid_id"])){
				$this->param["company_mid_id"] = $this->param["mid_id"];
				unset($this->param["mid_id"]);
			}
		}
		$this->param['timestamp'] = $this->now();
		$hashcode = AcquiredConfig::HASHCODE;
		$this->param["request_hash"] = $this->sha256hash($this->param,$hashcode);
	}

	/**
	 * generate response hash
	 */
	function generateResHash($param){
		$hashcode = AcquiredConfig::HASHCODE;
		return $this->responseHash($param,$hashcode);
	}

	function createData(){
		$this->setBasicParam();
	}

	function createJson(){
		$data = $this->createData();
		return json_encode($data);
	}

	function postJson(){
		$json = $this->createJson();
		$response = $this->https_request($this->url,$json,$this->curl_timeout,"json");
		$this->clearParam();
		return json_decode($response,true);
	}
	
}

class Auth_pub extends AcquiredPub
{
	function __construct(){
		$this->url = AcquiredConfig::REQUIREURL;		
		$this->curl_timeout = AcquiredConfig::CURLTIMEOUT;
	}

	function checkData(){
		if(!in_array(strtoupper($this->param["transaction_type"]), array("AUTH_ONLY","AUTH_CAPTURE"))){
			throw new SDKException("ERROR: Require transaction_type");
		}
		
		if(empty($this->param["merchant_order_id"])){
			throw new SDKException("ERROR: Require merchant_order_id");
		}

		if(empty($this->param["amount"])){
			throw new SDKException("ERROR: Require amount");
		}

		if(empty($this->param["currency_code_iso3"])){
			throw new SDKException("ERROR: Require currency_code_iso3");
		}

		if(empty($this->param["cardnumber"])){
			throw new SDKException("ERROR: Require cardnumber");
		}

		if(empty($this->param["card_type"])){
			throw new SDKException("ERROR: Require card_type");
		}

		if(empty($this->param["cardexp"])){
			throw new SDKException("ERROR: Require cardexp");
		}
	}

	function createData(){
		try{			
			$this->checkData();
			
			$this->setBasicParam();
			
			$transactionParam = array(
				'merchant_order_id',
				'transaction_type',
				'subscription_type',
				'amount',
				'currency_code_iso3',
				'merchant_customer_id',
				'merchant_custom_1',
				'merchant_custom_2',
				'merchant_custom_3'
			);
			$customerParam = array(
				'customer_title',
				'customer_fname',
				'customer_mname',
				'customer_lname',
				'customer_gender',
				'customer_dob',
				'customer_ipaddress',
				'customer_company'
			);
			$billingParam = array(
				'cardholder_name',
				'cardnumber',
				'card_type',
				'cardcvv',
				'cardexp',
				'billing_street',
				'billing_street2',
				'billing_city',
				'billing_state',
				'billing_zipcode',
				'billing_country_code_iso2',
				'billing_phone',
				'billing_email'
			);
			$tdsParam = array(
				'action',
				'pares',
				'ipaddress'
			);

			$data = array();
			foreach($this->param as $k=>$v){
				if(in_array($k, $transactionParam)){
					$data['transaction'][$k] = $v;
					continue;
				}
				if(in_array($k, $customerParam)){
					$data['customer'][$k] = $v;
					continue;
				}
				if(in_array($k, $billingParam)){
					$data['billing'][$k] = $v;
					continue;
				}
				if(in_array($k, $tdsParam)){
					$data['tds'][$k] = $v;
					continue;
				}
				$data[$k] = $v;
			}			

			return $data;

		}catch(SDKException $e){
			die($e->errorMessage());
		}
	}

	function createRebillData(){
		try{
			if(empty($this->param["amount"])){
				throw new SDKException("ERROR: Require amount");
			}

			if(empty($this->param["currency_code_iso3"])){
				throw new SDKException("ERROR: Require currency_code_iso3");
			}

			$this->param['subscription_type'] = "REBILL";

			$this->setBasicParam();
			
			$transactionParam = array(
				'merchant_order_id',
				'transaction_type',
				'subscription_type',
				'amount',
				'currency_code_iso3',
				'original_transaction_id'
			);

			$data = array();
			foreach($this->param as $k=>$v){
				if(in_array($k, $transactionParam)){
					$data['transaction'][$k] = $v;
					continue;
				}
				$data[$k] = $v;
			}
			return $data;
		}catch(SDKException $e){
			die($e->errorMessage());
		}
	}

	function rebillJson(){
		$data = $this->createRebillData();
		$json = json_encode($data);
		$response = $this->https_request($this->url,$json,$this->curl_timeout,"json");
		return json_decode($response,true);
	}

	function checkACSData(){

		if(empty($this->param['pareq'])){
			throw new SDKException("ERROR: Require pareq");
		}

		if(empty($this->param['termurl'])){
			throw new SDKException("ERROR: Require termurl");
		}

		if(empty($this->param['ACS_url'])){
			throw new SDKException("ERROR: Require ACS_url");
		}

		if(empty($this->param['md'])){
			throw new SDKException("ERROR: Require md");
		}

	}	

	function postToACS(){
		$this->checkACSData();

		$url = $this->param['ACS_url'];
		$pareq = $this->param['pareq'];
		$termurl = $this->param['termurl'];	
		$mdstr = $this->param['md'];

		$post_data = "pareq=".$pareq."&termurl=".$termurl."&md=".$mdstr;
		$response = $this->https_request($url,$post_data,$this->curl_timeout);
		$this->clearParam();
		return $response;

	}

	function checkSettleACSData(){
		if(empty($this->param['pares'])){
			throw new SDKException("ERROR: Invalid pares");
		}
		
		if(!in_array(strtoupper($this->param["transaction_type"]), array("AUTH_ONLY","AUTH_CAPTURE"))){
			throw new SDKException("ERROR: Invalid transaction_type");
		}
		if(empty($this->param['original_transaction_id'])){
			throw new SDKException("ERROR: Invalid original_transaction_id");
		}
		if(empty($this->param['merchant_order_id'])){
			throw new SDKException("ERROR: Invalid merchant_order_id");
		}
		if(empty($this->param['amount'])){
			throw new SDKException("ERROR: Invalid amount");
		}
		if(empty($this->param['currency_code_iso3'])){
			throw new SDKException("ERROR: Invalid currency_code_iso3");
		}
		
	}

	function createSettlementACS(){
		$this->param['action'] = "SETTLEMENT";

		$this->checkSettleACSData();

		$this->setBasicParam();

		$transactionParam = array(
			'merchant_order_id',
			'transaction_type',
			'subscription_type',
			'amount',
			'currency_code_iso3',
			'original_transaction_id'
		);
		$tdsParam = array(
			'action',
			'pares'
		);

		$data = array();
		foreach($this->param as $k=>$v){
			if(in_array($k, $transactionParam)){
				$data['transaction'][$k] = $v;
				continue;
			}
			if(in_array($k, $tdsParam)){
				$data['tds'][$k] = $v;
				continue;
			}
			$data[$k] = $v;
		}

		return $data;

	}

	function postSettleACS(){
		$data = $this->createSettlementACS();
		var_dump($data);exit;
		$json = json_encode($data);
		$response = $this->https_request($this->url,$json,$this->curl_timeout,"json");
		$this->clearParam();
		return json_decode($response,true);
	}

}

class Auth_only_pub extends Auth_pub
{
	function __construct(){
		$this->url = AcquiredConfig::REQUIREURL;		
		$this->curl_timeout = AcquiredConfig::CURLTIMEOUT;
		$this->param["transaction_type"] = "AUTH_ONLY";
	}
}

class Capture_pub extends AcquiredPub
{

	function checkData(){
		if(empty($this->param["original_transaction_id"])){
			throw new SDKException("ERROR: Require original_transaction_id");
		}

		if(empty($this->param["amount"])){
			throw new SDKException("ERROR: Require amount");
		}
	}

	function createData(){
		try{
			$this->checkData();

			$this->param["transaction_type"] = "CAPTURE";
			$this->setBasicParam();
			$transactionParam = array("transaction_type","original_transaction_id","amount");
			$data = array();
			foreach($this->param as $k=>$v){
				if(in_array($k, $transactionParam)){
					$data['transaction'][$k] = $v;
					continue;
				}
				$data[$k] = $v;
			}
			return $data;
		}catch(SDKException $e){
			die($e->errorMessage());
		}
	}

}

class Auth_capture_pub extends Auth_pub
{
	function __construct(){
		$this->url = AcquiredConfig::REQUIREURL;		
		$this->curl_timeout = AcquiredConfig::CURLTIMEOUT;
		$this->param["transaction_type"] = "AUTH_CAPTURE";
	}
}

class Void_pub extends AcquiredPub
{
	function checkData(){
		if(empty($this->param["original_transaction_id"])){
			throw new SDKException("ERROR: Require original_transaction_id");
		}
	}

	function createData(){
		try{
			$this->checkData();

			$this->param["transaction_type"] = "VOID";
			$this->setBasicParam();
			$transactionParam = array("transaction_type","original_transaction_id");
			$data = array();
			foreach($this->param as $k=>$v){
				if(in_array($k, $transactionParam)){
					$data['transaction'][$k] = $v;
					continue;
				}
				$data[$k] = $v;
			}
			return $data;
		}catch(SDKException $e){
			die($e->errorMessage());
		}
	}
}

class Refund_pub extends AcquiredPub
{
	function checkData(){
		if(empty($this->param["original_transaction_id"])){
			throw new SDKException("ERROR: Require original_transaction_id");
		}

		if(empty($this->param["amount"])){
			throw new SDKException("ERROR: Require amount");
		}
	}

	function createData(){
		try{
			$this->checkData();

			$this->param["transaction_type"] = "REFUND";
			$this->setBasicParam();
			$transactionParam = array("transaction_type","original_transaction_id","amount");
			$data = array();
			foreach($this->param as $k=>$v){
				if(in_array($k, $transactionParam)){
					$data['transaction'][$k] = $v;
					continue;
				}
				$data[$k] = $v;
			}
			return $data;
		}catch(SDKException $e){
			die($e->errorMessage());
		}
	}
}

class Credit_pub extends AcquiredPub
{
	function checkData(){
		
		if(empty($this->param["merchant_order_id"])){
			throw new SDKException("ERROR: Require merchant_order_id");
		}

		if(empty($this->param["amount"])){
			throw new SDKException("ERROR: Require amount");
		}

		if(empty($this->param["currency_code_iso3"])){
			throw new SDKException("ERROR: Require currency_code_iso3");
		}

		if(empty($this->param["cardnumber"])){
			throw new SDKException("ERROR: Require cardnumber");
		}

		if(empty($this->param["card_type"])){
			throw new SDKException("ERROR: Require card_type");
		}

		if(empty($this->param["cardexp"])){
			throw new SDKException("ERROR: Require cardexp");
		}
	}

	function createData(){
		try{			
			$this->checkData();
			
			$this->setParam('transaction_type','CREDIT');
			$this->setBasicParam();
			
			$transactionParam = array(
				'merchant_order_id',
				'transaction_type',
				'amount',
				'currency_code_iso3',
				'merchant_customer_id',
				'merchant_custom_1',
				'merchant_custom_2',
				'merchant_custom_3'
			);
			$customerParam = array(
				'customer_title',
				'customer_fname',
				'customer_mname',
				'customer_lname',
				'customer_gender',
				'customer_dob',
				'customer_ipaddress',
				'customer_company'
			);
			$billingParam = array(
				'cardholder_name',
				'cardnumber',
				'card_type',
				'cardcvv',
				'cardexp',
				'billing_street',
				'billing_street2',
				'billing_city',
				'billing_state',
				'billing_zipcode',
				'billing_country_code_iso2',
				'billing_phone',
				'billing_email'
			);			

			$data = array();
			foreach($this->param as $k=>$v){
				if(in_array($k, $transactionParam)){
					$data['transaction'][$k] = $v;
					continue;
				}
				if(in_array($k, $customerParam)){
					$data['customer'][$k] = $v;
					continue;
				}
				if(in_array($k, $billingParam)){
					$data['billing'][$k] = $v;
					continue;
				}
				$data[$k] = $v;
			}

			if(isset($this->param['action']) and $this->param['action'] == 'ENQUIRE'){
				$data['tds']['action'] = $this->param['action'];
			}

			return $data;

		}catch(SDKException $e){
			die($e->errorMessage());
		}
	}
}

class Subscription_manage_pub extends AcquiredPub
{
	function checkData(){
		if(empty($this->param["original_transaction_id"])){
			throw new SDKException("ERROR: Require original_transaction_id");
		}
	}

	function createData(){
		try{
			$this->checkData();

			$this->param["transaction_type"] = "SUBSCRIPTION_MANAGE";
			$this->param["subscription_type"] = "UPDATE_BILLING";
			$this->setBasicParam();

			$transactionParam = array("transaction_type","subscription_type","original_transaction_id");
			$customerParam = array(
				'customer_title',
				'customer_fname',
				'customer_mname',
				'customer_lname',
				'customer_gender',
				'customer_dob',
				'customer_ipaddress',
				'customer_company'
			);
			$billingParam = array(
				'cardholder_name',
				'cardnumber',
				'card_type',
				'cardcvv',
				'cardexp',
				'billing_street',
				'billing_street2',
				'billing_city',
				'billing_state',
				'billing_zipcode',
				'billing_country_code_iso2',
				'billing_phone',
				'billing_email'
			);			

			$data = array();
			foreach($this->param as $k=>$v){
				if(in_array($k, $transactionParam)){
					$data['transaction'][$k] = $v;
					continue;
				}
				if(in_array($k, $customerParam)){
					$data['customer'][$k] = $v;
					continue;
				}
				if(in_array($k, $billingParam)){
					$data['billing'][$k] = $v;
					continue;
				}
				$data[$k] = $v;
			}
			return $data;
		}catch(SDKException $e){
			die($e->errorMessage());
		}
	}
}
