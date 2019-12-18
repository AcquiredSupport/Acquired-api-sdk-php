<?php
namespace Acquired;

/**
 * set account config
 */

class AcquiredConfig
{
	
	const COMPANYID = '';
	const COMPANYPASS = '';
	const COMPANYMIDID = '';
	const HASHCODE = '';

	//require api url
	const REQUIREURL = "";
	//QA: https://qaapi.acquired.com/api.php
	//PROD: https://gateway.acquired.com/api.php

	const VERIFY_CARD_URL = "";
	//QA: https://qaapi.acquired.com/api.php/forward/tds_version

	//logs path
	const LOGPATH = '../logs/';

	// curl timeout
	const CURLTIMEOUT = 120;
	
}
