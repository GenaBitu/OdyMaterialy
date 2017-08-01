<?php
const _API_EXEC = 1;

require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/API/internal/APIException.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/API/internal/ArgumentException.php');

use Ramsey\Uuid\Uuid;

function getImage()
{
	if(!isset($_GET['id']))
	{
		throw new OdyMaterialyAPI\ArgumentException(OdyMaterialyAPI\ArgumentException::GET, 'id');
	}
	$id = Uuid::fromString($_GET['id'])->__toString();

	$file = $_SERVER['DOCUMENT_ROOT'] . '/images/' . $id . '.jpg';

	if(!file_exists($file))
	{
		http_response_code(404);
		return;
	}

	header('content-type: image/jpeg');
	header('content-length: ' . filesize($file));

	$modified = filemtime($file);
	if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
	{
		$ifMod = new DateTime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
		if($ifMod->format('U') > $modified)
		{
			http_response_code(304);
			return;
		}
	}

	header('last-modified: ' . date('r', $modified));
	readfile($file);
}

try
{
	getImage();
}
catch(OdyMaterialyAPI\APIException $e)
{
	echo($e);
}
