<?php

namespace Acquired\Service;

use Acquired\AcquiredConfig;
use Acquired\AcquiredException;

class PayOutHandle extends HandlePub
{
	public function checkData(){
		
	}

	public function createData(){
		try{
			$this->checkData();

			$this->param["transaction_type"] = "PAY_OUT";
			$this->setBasicParam();

			$transactionParam = array(
				'transaction_type',
				'merchant_order_id',
				'original_transaction_id',
				'amount',
				'reference',
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
}