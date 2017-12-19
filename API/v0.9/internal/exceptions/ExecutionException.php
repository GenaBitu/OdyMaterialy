<?php
namespace OdyMaterialyAPI;

@_API_EXEC === 1 or die('Restricted access.');

require_once($_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/internal/exceptions/Exception.php');

class ExecutionException extends Exception
{
	const TYPE = 'ExecutionException';
	const STATUS = 500;

	public function __construct($query, $statement)
	{
		parent::__construct('Query "' . $query . '" has failed. Error message: "' . $statement->error . '".');
	}
}
