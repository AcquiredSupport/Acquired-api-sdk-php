<?php

namespace Acquired\Service;
/**
 * 
 * 	AuthHandle  -- deal AUTH_ONLY and AUTH_CAPTURE request
 * 		checkData -- verify the required data
 *		createData -- create request array data
 * 		createRebillData -- create rebill request array data
 *		rebillJson -- curl rebill api
 *		checkACSData -- check post to ACS Data
 *		postToACS -- post to ACS
 *		checkSettleACSData -- verify the request settlement data
 *		createSettlementACS -- create settlement request array data
 *		postSettleACS -- curl ACS settlement api
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
			throw new AcquiredException("ERROR: Require amount");
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

			$data = array();
			foreach($this->param as $k=>$v){
				if(in_array($k, $transactionParam)){
					$data['transaction'][$k] = $v;
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

	public function checkACSData(){

		if(empty($this->param['pareq'])){
			throw new AcquiredException("ERROR: Require pareq");
		}

		if(empty($this->param['termurl'])){
			throw new AcquiredException("ERROR: Require termurl");
		}

		if(empty($this->param['ACS_url'])){
			throw new AcquiredException("ERROR: Require ACS_url");
		}

		if(empty($this->param['md'])){
			throw new AcquiredException("ERROR: Require md");
		}

	}	

	public function postToACS(){
		$this->checkACSData();

		$url = $this->param['ACS_url'];
		$pareq = $this->param['pareq'];
		$termurl = $this->param['termurl'];	
		$mdstr = $this->param['md'];

		$post_data = "pareq=".$pareq."&termurl=".$termurl."&md=".$mdstr;
		$response = $this->httpsRequest($url,$post_data,$this->curl_timeout);
		$this->clearParam();
		return $response;

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

	public function postSettleACS(){
		$data = $this->createSettlementACS();
		$json = json_encode($data);
		$response = $this->httpsRequest($this->url,$json,$this->curl_timeout,"json");
		$this->clearParam();
		return json_decode($response,true);
	}

}