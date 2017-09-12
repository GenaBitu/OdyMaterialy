<?php
const _API_EXEC = 1;

require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/API/internal/Competence.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/API/internal/Database.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/API/internal/Endpoint.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/API/internal/exceptions/ArgumentException.php');

use Ramsey\Uuid\Uuid;

$endpoint = new OdyMaterialyAPI\Endpoint('competence');

$listCompetences = function($skautis, $data)
{
	$SQL = <<<SQL
SELECT id, number, name, description
FROM competences
ORDER BY number;
SQL;

	$db = new OdymaterialyAPI\Database();
	$db->prepare($SQL);
	$db->execute();
	$id = '';
	$number = '';
	$name = '';
	$description = '';
	$db->bind_result($id, $number, $name, $description);
	$competences = array();
	while($db->fetch())
	{
		$competences[] = new OdyMaterialyAPI\Competence($id, $number, $name, $description);
	}
	return ['status' => 200, 'response' => $competences];
};
$endpoint->setListMethod(new OdymaterialyAPI\Role('guest'), $listCompetences);

$addCompetence = function($skautis, $data)
{
	$SQL = <<<SQL
INSERT INTO competences (id, number, name, description)
VALUES (?, ?, ?, ?);
SQL;

	if(!isset($data['number']))
	{
		throw new OdyMaterialyAPI\ArgumentException(OdyMaterialyAPI\ArgumentException::POST, 'number');
	}
	if(!isset($data['name']))
	{
		throw new OdyMaterialyAPI\ArgumentException(OdyMaterialyAPI\ArgumentException::POST, 'name');
	}
	$number = $data['number'];
	$name = $data['name'];
	$description = '';
	if(isset($data['description']))
	{
		$description = $data['description'];
	}
	$uuid = Uuid::uuid4()->getBytes();

	$db = new OdymaterialyAPI\Database();
	$db->prepare($SQL);
	$db->bind_param('siss', $uuid, $number, $name, $description);
	$db->execute();
	return ['status' => 201];
};
$endpoint->setAddMethod(new OdymaterialyAPI\Role('administrator'), $addCompetence);

$updateCompetence = function($skautis, $data)
{
	$selectSQL = <<<SQL
SELECT number, name, description
FROM competences
WHERE id = ?;
SQL;
	$updateSQL = <<<SQL
UPDATE competences
SET number = ?, name = ?, description = ?
WHERE id = ?
LIMIT 1;
SQL;

	$id = $data['id']->getBytes();
	if(isset($data['number']))
	{
		$number = $data['number'];
	}
	if(isset($data['name']))
	{
		$name = $data['name'];
	}
	if(isset($data['description']))
	{
		$description = $data['description'];
	}

	$db = new OdymaterialyAPI\Database();

	if(!isset($number) or !isset($name) or !isset($description))
	{
		$db->prepare($selectSQL);
		$db->bind_param('s', $id);
		$db->execute();
		$db->store_result();
		$origNumber = '';
		$origName = '';
		$origDescription = '';
		$db->bind_result($origNumber, $origName, $origDescription);
		$db->fetch_require('competence');
		if(!isset($number))
		{
			$number = $origNumber;
		}
		if(!isset($name))
		{
			$name = $origName;
		}
		if(!isset($description))
		{
			$description = $origDescription;
		}
	}

	$db->prepare($updateSQL);
	$db->bind_param('isss', $number, $name, $description, $id);
	$db->execute();
	return ['status' => 200];
};
$endpoint->setUpdateMethod(new OdymaterialyAPI\Role('administrator'), $updateCompetence);

$deleteCompetence = function($skautis, $data)
{
	$deleteLessonsSQL = <<<SQL
DELETE FROM competences_for_lessons
WHERE competence_id = ?;
SQL;
	$deleteSQL = <<<SQL
DELETE FROM competences
WHERE id = ?
LIMIT 1;
SQL;

	$id = $data['id']->getBytes();

	$db = new OdymaterialyAPI\Database();
	$db->start_transaction();

	$db->prepare($deleteLessonsSQL);
	$db->bind_param('s', $id);
	$db->execute();

	$db->prepare($deleteSQL);
	$db->bind_param('s', $id);
	$db->execute();

	$db->finish_transaction();
	return ['status' => 200];
};
$endpoint->setDeleteMethod(new OdymaterialyAPI\Role('administrator'), $deleteCompetence);

$endpoint->handle();
