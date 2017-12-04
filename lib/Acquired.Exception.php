<?php
/**
 * exception class
 */
class SDKException extends Exception {
	public function errorMessage()
	{
		return $this->getMessage();
	}
}
