<?php

namespace Acquired\Service;

use Acquired\AcquiredConfig;
use Acquired\AcquiredException;

class AuthCaptureHandle extends AuthHandle
{
	public function __construct(){
		$this->url = AcquiredConfig::REQUIREURL;		
		$this->curl_timeout = AcquiredConfig::CURLTIMEOUT;
		$this->param["transaction_type"] = "AUTH_CAPTURE";
	}
}