<?php

namespace Acquired;

use Exception;
/**
 * exception class
 */
class AcquiredException extends Exception {
	public function errorMessage()
	{
		return $this->getMessage();
	}
}
