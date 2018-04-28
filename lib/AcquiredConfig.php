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

	//logs path
	const LOGPATH = '../logs/';

	// curl timeout
	const CURLTIMEOUT = 120;
	
}
