<?php

namespace Acquired\Service;

use Acquired\AcquiredConfig;
use Acquired\AcquiredException;

class CreditHandle extends HandlePub
{
	public function checkData(){
		
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

		}catch(AcquiredException $e){
			die($e->errorMessage());
		}
	}
}