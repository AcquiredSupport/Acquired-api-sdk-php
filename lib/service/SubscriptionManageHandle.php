<?php

namespace Acquired\Service;

use Acquired\AcquiredConfig;
use Acquired\AcquiredException;

class SubscriptionManageHandle extends HandlePub
{
	public function checkData(){
		if(empty($this->param["original_transaction_id"])){
			throw new AcquiredException("ERROR: Require original_transaction_id");
		}
	}

	public function createData(){
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
		}catch(AcquiredException $e){
			die($e->errorMessage());
		}
	}
}