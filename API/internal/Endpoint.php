<?php
namespace OdyMaterialyAPI;

@_API_EXEC === 1 or die('Restricted access.');

require_once($_SERVER['DOCUMENT_ROOT'] . '/API/internal/skautisTry.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/API/internal/exceptions/Exception.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/API/internal/exceptions/MissingArgumentException.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/API/internal/exceptions/NotFoundException.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/API/internal/exceptions/NotImplementedException.php');

class Endpoint
{
	private $resourceName;
	private $subEndpoints;

	private $listFunction;
	private $listRole;
	
	private $getFunction;
	private $getRole;

	private $updateFunction;
	private $updateRole;

	private $addFunction;
	private $addRole;

	private $deleteFunction;
	private $deleteRole;

	public function __construct($resourceName)
	{
		$this->resourceName = $resourceName;
		$this->subEndpoints = [];

		$this->listFunction = function() {throw new NotImplementedException();};
		$this->listRole = new Role('guest');

		$this->getFunction = function() {throw new NotImplementedException();};
		$this->getRole = new Role('guest');

		$this->updateFunction = function() {throw new NotImplementedException();};
		$this->updateRole = new Role('guest');

		$this->addFunction = function() {throw new NotImplementedException();};
		$this->addRole = new Role('guest');

		$this->deleteFunction = function() {throw new NotImplementedException();};
		$this->deleteRole = new Role('guest');
	}

	public function addSubEndpoint($name, $endpoint)
	{
		$this->subEndpoints[$name] = $endpoint;
	}

	public function setListMethod($minimalRole, $callback)
	{
		$this->listRole = $minimalRole;
		$this->listFunction = $callback;
	}

	public function setGetMethod($minimalRole, $callback)
	{
		$this->getRole = $minimalRole;
		$this->getFunction = $callback;
	}

	public function setUpdateMethod($minimalRole, $callback)
	{
		$this->updateRole = $minimalRole;
		$this->updateFunction = $callback;
	}

	public function setAddMethod($minimalRole, $callback)
	{
		$this->addRole = $minimalRole;
		$this->addFunction = $callback;
	}

	public function setDeleteMethod($minimalRole, $callback)
	{
		$this->deleteRole = $minimalRole;
		$this->deleteFunction = $callback;
	}

	public function call($method, $data)
	{
		switch($method)
		{
		case 'GET':
			if(isset($data['id']))
			{
				$func = $this->getFunction;
				$role = $this->getRole;
			}
			else
			{
				$func = $this->listFunction;
				$role = $this->listRole;
			}
			break;
		case 'PUT':
			if(isset($data['id']) or isset($data['parent-id']))
			{
				$func = $this->updateFunction;
				$role = $this->updateRole;
			}
			else
			{
				throw new MissingArgumentException(MissingArgumentException::POST, 'id');
			}
			break;
		case 'POST':
			$func = $this->addFunction;
			$role = $this->addRole;
			break;
		case 'DELETE':
			if(isset($data['id']) or isset($data['parent-id']))
			{
				$func = $this->deleteFunction;
				$role = $this->deleteRole;
			}
			else
			{
				throw new MissingArgumentException(MissingArgumentException::GET, 'id');
			}
			break;
		}
		$wrapper = function($skautis) use ($data, $func)
		{
			return $func($skautis, $data);
		};
		$hardCheck = (Role_cmp($role, new Role('user')) > 0);
		$ret = roleTry($wrapper, $hardCheck, $role);
		if(isset($ret))
		{
			return $ret;
		}
	}

	public function handle_self($method, $data)
	{
		try
		{
			if(isset($data['parent-id']))
			{
				try
				{
					$data['parent-id'] = \Ramsey\Uuid\Uuid::fromString($data['parent-id']);
				}
				catch(\Ramsey\Uuid\Exception\InvalidUuidStringException $e)
				{
					throw new NotFoundException('resource'); // TODO: FIX
				}
			}
			if(isset($data['id']))
			{
				try
				{
					$data['id'] = \Ramsey\Uuid\Uuid::fromString($data['id']);
				}
				catch(\Ramsey\Uuid\Exception\InvalidUuidStringException $e)
				{
					throw new NotFoundException($this->resourceName);
				}
			}
			header('content-type: application/json; charset=utf-8');
			$ret = $this->call($method, $data);
		}
		catch(Exception $e)
		{
			header('content-type:application/json; charset=utf-8');
			$ret = $e->handle();
		}
		if(isset($ret))
		{
			http_response_code($ret['status']);
			echo(json_encode($ret, JSON_UNESCAPED_UNICODE));
		}
	}

	public function handle()
	{
		$method = $_SERVER['REQUEST_METHOD'];
		switch($method)
		{
			case 'GET':
			case 'DELETE':
				$data = $_GET;
				break;
			case 'PUT':
				parse_str(file_get_contents("php://input"), $data);
				break;
			case 'POST':
				$data = $_POST;
				break;
		}
		if(isset($_GET['id']) and $_GET['id'] !== '')
		{
			$data['id'] = $_GET['id'];
		}
		else
		{
			unset($data['id']);
		}

		if(isset($data['id']) and isset($_GET['sub-resource']) and $_GET['sub-resource'] !== '')
		{
		   	if(isset($this->subEndpoints[$_GET['sub-resource']]))
			{
				$data['parent-id'] = $data['id'];
				if(isset($_GET['sub-id']) and $_GET['sub-id'] !== '')
				{
					$data['id'] = $_GET['sub-id'];
				}
				else
				{
					unset($data['id']);
				}
				unset($data['sub-id']);
				$this->subEndpoints[$_GET['sub-resource']]->handle_self($method, $data);
			}
			else
			{
				http_response_code(404);
				include_once($_SERVER['DOCUMENT_ROOT'] . '/error/404.html');
				die();
			}
		}
		else
		{
			$this->handle_self($method, $data);
		}
	}
}
