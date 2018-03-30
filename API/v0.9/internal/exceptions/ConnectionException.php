<?php declare(strict_types = 1);
namespace HandbookAPI;

@_API_EXEC === 1 or die('Restricted access.');

require_once($_SERVER['DOCUMENT_ROOT'] . '/api-config.php');
require_once($CONFIG->basepath . '/v0.9/internal/exceptions/Exception.php');

class ConnectionException extends Exception
{
	const TYPE = 'ConnectionException';
	const STATUS = 500;

	public function __construct($db)
	{
		parent::__construct('Database connection request failed. Error message: "' . $db->errorInfo()[2] . '".');
	}
}
