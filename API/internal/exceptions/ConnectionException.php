<?php
namespace OdyMaterialyAPI;

@_API_EXEC === 1 or die('Restricted access.');

require_once($_SERVER['DOCUMENT_ROOT'] . '/API/internal/exceptions/Exception.php');

class ConnectionException extends Exception
{
	const TYPE = 'ConnectionException';
	const STATUS = 500;

	public function __construct($db)
	{
		parent::__construct('Database connection request failed. Error message: "' . $db->connect_error . '".');
	}
}