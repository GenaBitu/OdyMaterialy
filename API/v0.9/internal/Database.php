<?php declare(strict_types=1);
namespace OdyMaterialyAPI;

@_API_EXEC === 1 or die('Restricted access.');

require_once($_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/internal/database.secret.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/internal/exceptions/ConnectionException.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/internal/exceptions/ExecutionException.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/internal/exceptions/NotFoundException.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/internal/exceptions/QueryException.php');

class Database
{
	private static $db;
	private static $instanceCount;
	private $SQL;
	private $statement;

	public function __construct()
	{
		try
		{
			self::$db = new \PDO(DB_DSN . ';charset=utf8', DB_USER, DB_PASSWORD);
			self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch(PDOException $e)
		{
			throw new ConnectionException(self::$db);
		}
		self::$instanceCount = self::$instanceCount + 1;
	}

	public function prepare(string $SQL) : void
	{
		$this->SQL = $SQL;
		try
		{
			$this->statement = self::$db->prepare($this->SQL);
		}
		catch(PDOException $e)
		{
			throw new QueryException($this->SQL, self::$db);
		}
	}

	public function bindParam(string $name, &$value) : void
	{
		$this->statement->bindParam($name, $value);
	}

	public function execute(string $resourceName = "") : void
	{
		if(!$this->statement->execute())
		{
			if($this->statement->errorCode() == 23000) // Foreign key constraint fail
			{
				throw new NotFoundException($resourceName);
			}
			throw new ExecutionException($this->SQL, $this->statement);
		}
	}

	public function bindColumn(string $name, &$value) : void
	{
		$this->statement->bindColumn(...$vars);
	}

	public function fetch() : bool
	{
		return $this->statement->fetch(PDO::FETCH_BOUND);
	}

	public function fetchRequire(string $resourceName) : void
	{
		if(!$this->fetch())
		{
			throw new NotFoundException($resourceName);
		}
	}

	public function fetchAll() : array
	{
		return $this->statement->fetchAll(PDO::FETCH_ASSOC);
	}

	public function beginTransaction() : void
	{
		self::$db->beginTransaction();
	}

	public function endTransaction() : void
	{
		self::$db->commit();
	}

	public function __destruct()
	{
		if(isset($this->statement))
		{
			$this->statement = null;
		}
		self::$instanceCount = self::$instanceCount - 1;
		if(self::$instanceCount === 0)
		{
			self::$db = null;
		}
	}
}
