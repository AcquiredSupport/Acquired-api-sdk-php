<?php

namespace Acquired\Service;

use Acquired\AcquiredConfig;
use Acquired\AcquiredException;

class CaptureHandle extends HandlePub
{

	public function checkData(){
		if(empty($this->param["original_transaction_id"])){
			throw new AcquiredException("ERROR: Require original_transaction_id");
		}

		if(empty($this->param["amount"])){
			throw new AcquiredException("ERROR: Require amount");
		}
	}

	public function createData(){
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
		}catch(AcquiredException $e){
			die($e->errorMessage());
		}
	}

}