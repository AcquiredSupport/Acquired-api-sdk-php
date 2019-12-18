<?php

namespace Acquired\Service;

use Acquired\AcquiredConfig;
use Acquired\AcquiredException;

class BeneficiaryNewHandle extends HandlePub
{
	public function checkData(){
		
	}

	public function createData(){
		try{
			$this->checkData();

			$this->param["transaction_type"] = "BENEFICIARY_NEW";
			$this->setBasicParam();

			$transactionParam = array(
				'transaction_type',
				'original_transaction_id',
				'merchant_order_id',
				'merchant_customer_id',
				'merchant_custom_1',
				'merchant_custom_2',
				'merchant_custom_3'
			);
			$customerParam = array(
				'customer_fname',
				'customer_lname',
			);
			$billingParam = array(
				'billing_street',
				'billing_street2',
				'billing_city',
				'billing_state',
				'billing_zipcode',
				'billing_country_code_iso2',
				'billing_phone',
				'billing_email'
			);
			$accountParam = array(
				'sort_code',
				'account_number'
			);
			$linkParam = array(
				'card'
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
				if(in_array($k, $accountParam)){
					$data['account'][$k] = $v;
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
}