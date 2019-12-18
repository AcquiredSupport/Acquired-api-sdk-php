<?php

namespace Acquired\Service;
/**
 * 
 * 	AuthHandle  -- deal AUTH_ONLY and AUTH_CAPTURE request
 * 		checkData -- verify the required data
 *		createData -- create request array data
 * 		createRebillData -- create rebill request array data
 *		rebillJson -- curl rebill api
 *		checkSettleACSData -- 3-DS version 1: verify the request settlement data
 *		createSettlementACS -- 3-DS version 1: create settlement request array data
 *		postSettleACS -- 3-DS version 1: curl ACS settlement api
 *		verifyCard -- verify cardnumber if support 3-DS version 2
 *		checkSettleACSDatav2 -- 3-DS version 2: verify the request settlement data
 *		createSettlementACSv2 -- 3-DS version 2: create settlement request array data
 *		postSettleACSv2 -- 3-DS version 2: curl ACS settlement api
 * 
 */

use Acquired\AcquiredConfig;
use Acquired\AcquiredException;

class AuthHandle extends HandlePub
{
	public function __construct(){
		$this->url = AcquiredConfig::REQUIREURL;		
		$this->curl_timeout = AcquiredConfig::CURLTIMEOUT;
	}

	public function checkData(){
		if(!in_array(strtoupper($this->param["transaction_type"]), array("AUTH_ONLY","AUTH_CAPTURE"))){
			throw new AcquiredException("ERROR: Require transaction_type");
		}
		
		if(empty($this->param["merchant_order_id"])){
			throw new AcquiredException("ERROR: Require merchant_order_id");
		}

		if(empty($this->param["amount"])){
			$this->param["amount"] = 0;
		}

		if(empty($this->param["currency_code_iso3"])){
			throw new AcquiredException("ERROR: Require currency_code_iso3");
		}

		if(empty($this->param["cardnumber"])){
			throw new AcquiredException("ERROR: Require cardnumber");
		}

		if(empty($this->param["card_type"])){
			throw new AcquiredException("ERROR: Require card_type");
		}

		if(empty($this->param["cardexp"])){
			throw new AcquiredException("ERROR: Require cardexp");
		}
	}

	public function createData(){
		try{			
			$this->checkData();
			
			$this->setBasicParam();
			
			$transactionParam = array(
				'original_transaction_id',
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
				'ipaddress',
				'cavv',
				'xid',
				'eci',
				'status',
				'enrolled',
				'server_trans_id',
				'version',
				'ds_trans_id',
				'acs_trans_id',
				'source',
				'type',
				'method_url_complete'
			);
			$tdsBrowserParam = array(
				'accept_header',
				'ip',
				'java_enabled',
				'javascript_enabled',
				'language',
				'screen_height',
				'screen_width',
				'challenge_window_size',
				'timezone',
				'user_agent',
				'color_depth'
			);
			$tdsMerchantParam = array(
				'contact_url',
				'challenge_url'
			);
			$linkParam = array(
				'beneficiary'
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
				if(in_array($k, $tdsBrowserParam)){
					$data['tds']['browser_data'][$k] = $v;
					continue;	
				}
				if(in_array($k, $tdsMerchantParam)){
					$data['tds']['merchant'][$k] = $v;
					continue;
				}
				if(in_array($k, $linkParam)){
					$data['link'][$k] = $v;
					continue;
				}
				$data[$k] = $v;
			}

			return $data;

		}catch(AcquiredException $e){
			die($e->errorMessage());
		}
	}

	public function createRebillData(){
		try{
			if(empty($this->param["amount"])){
				throw new AcquiredException("ERROR: Require amount");
			}

			if(empty($this->param["currency_code_iso3"])){
				throw new AcquiredException("ERROR: Require currency_code_iso3");
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
			$linkParam = array(
				'pay_out'
			);

			$data = array();
			foreach($this->param as $k=>$v){
				if(in_array($k, $transactionParam)){
					$data['transaction'][$k] = $v;
					continue;
				}
				if(in_array($k, $linkParam)){
					$data['link'][$k] = $v;
					continue;
				}
				$data[$k] = $v;
			}
			return $data;
		}catch(AcquiredException $e){
			die($e->errorMessage());
		}
	}

	public function rebillJson(){
		$data = $this->createRebillData();
		$json = json_encode($data);
		$response = $this->httpsRequest($this->url,$json,$this->curl_timeout,"json");
		return json_decode($response,true);
	}

	public function createReuseData(){
		try{
			if(empty($this->param["amount"])){
				throw new AcquiredException("ERROR: Require amount");
			}

			if(empty($this->param["currency_code_iso3"])){
				throw new AcquiredException("ERROR: Require currency_code_iso3");
			}

			$this->param['subscription_type'] = "REUSE";

			$this->setBasicParam();
			
			$transactionParam = array(
				'merchant_order_id',
				'transaction_type',
				'subscription_type',
				'amount',
				'currency_code_iso3',
				'original_transaction_id',
			);
			$billingParam = array(
				'cardcvv',
			);

			$data = array();
			foreach($this->param as $k=>$v){
				if(in_array($k, $transactionParam)){
					$data['transaction'][$k] = $v;
					continue;
				}
				if(in_array($k, $billingParam)){
					$data['billing'][$k] = $v;
					continue;
				}
				$data[$k] = $v;
			}
			return $data;
		}catch(AcquiredException $e){
			die($e->errorMessage());
		}
	}

	public function reuseJson(){
		$data = $this->createReuseData();
		$json = json_encode($data);
		$response = $this->httpsRequest($this->url,$json,$this->curl_timeout,"json");
		return json_decode($response,true);
	}

	public function checkSettleACSData(){
		if(empty($this->param['pares'])){
			throw new AcquiredException("ERROR: Invalid pares");
		}
		
		if(!in_array(strtoupper($this->param["transaction_type"]), array("AUTH_ONLY","AUTH_CAPTURE"))){
			throw new AcquiredException("ERROR: Invalid transaction_type");
		}
		if(empty($this->param['original_transaction_id'])){
			throw new AcquiredException("ERROR: Invalid original_transaction_id");
		}
		if(empty($this->param['merchant_order_id'])){
			throw new AcquiredException("ERROR: Invalid merchant_order_id");
		}
		if(empty($this->param['amount'])){
			throw new AcquiredException("ERROR: Invalid amount");
		}
		if(empty($this->param['currency_code_iso3'])){
			throw new AcquiredException("ERROR: Invalid currency_code_iso3");
		}
		
	}

	public function createSettlementACS(){
		try{
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
			$billingParam = array(
				'cardcvv',
				'billing_street',
				'billing_zipcode'
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
		}catch(AcquiredException $e){
			die($e->errorMessage());
		}

	}

	/**
	 * [postSettleACS The final step for process 3-D secure v1]
	 * @return   [array][response]
	 */
	public function postSettleACS(){
		$data = $this->createSettlementACS();
		$json = json_encode($data);
		$response = $this->httpsRequest($this->url,$json,$this->curl_timeout,"json");
		$this->clearParam();
		return json_decode($response,true);
	}

	private function checkVerifyCardData(){		

		if(empty($this->param['currency_code_iso3'])){
			throw new AcquiredException("ERROR: Invalid currency_code_iso3");
		}

		if(empty($this->param['cardnumber'])){
			throw new AcquiredException("ERROR: Invalid cardnumber");
		}

		if(empty($this->param['method_notification_url'])){
			throw new AcquiredException("ERROR: Invalid method_notification_url");
		}

	}

	/**
	 * [verifyCard 3ds v2 verify card]
	 * @return   [array][response]
	 */
	public function verifyCard(){
		try{
			$this->checkVerifyCardData();
		}catch(AcquiredException $e){
			die($e->errorMessage());
		}

		$data["company_id"] = AcquiredConfig::COMPANYID;
		$data["company_mid_id"] = AcquiredConfig::COMPANYMIDID;
		$data["currency_code_iso3"] = isset($this->param["currency_code_iso3"]) ? $this->param["currency_code_iso3"] : '';
		$data["cardnumber"] = $this->param["cardnumber"];
		$data["method_notification_url"] = $this->param["method_notification_url"];
		
		$url = AcquiredConfig::VERIFY_CARD_URL;
		$json = json_encode($data);

		$response = $this->httpsRequest($url, $json, $this->curl_timeout, "json");

		return json_decode($response,true);
	}

	public function checkSettleACSDatav2(){
		if(empty($this->param['cres'])){
			throw new AcquiredException("ERROR: Invalid pares");
		}
		
		if(empty($this->param["transaction_type"]) || !in_array(strtoupper($this->param["transaction_type"]), array("AUTH_ONLY","AUTH_CAPTURE"))){
			throw new AcquiredException("ERROR: Invalid transaction_type");
		}
		if(empty($this->param['original_transaction_id'])){
			throw new AcquiredException("ERROR: Invalid original_transaction_id");
		}
		
	}

	public function createSettlementACSv2(){
		try{
			$this->param['action'] = "SCA_COMPLETE";

			$this->checkSettleACSDatav2();
			
			$this->setBasicParam();

			$transactionParam = array(
				'transaction_type',
				'original_transaction_id'
			);
			$tdsParam = array(
				'action',
				'cres'
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
		}catch(AcquiredException $e){
			die($e->errorMessage());
		}

	}

	/**
	 * [postSettleACSv2: The final step to process 3-D secure v2]
	 * @return   [array][response]
	 */
	public function postSettleACSv2(){
		$data = $this->createSettlementACSv2();
		$json = json_encode($data);
		$response = $this->httpsRequest($this->url,$json,$this->curl_timeout,"json");
		$this->clearParam();
		return json_decode($response,true);
	}

}