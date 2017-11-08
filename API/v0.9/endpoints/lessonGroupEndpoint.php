<?php
@_API_EXEC === 1 or die('Restricted access.');

require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/internal/Database.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/internal/Endpoint.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/internal/Role.php');

use Ramsey\Uuid\Uuid;

$lessonGroupEndpoint = new OdyMaterialyAPI\Endpoint('group');

$updateLessonGroups = function($skautis, $data, $endpoint)
{
	$deleteSQL = <<<SQL
DELETE FROM groups_for_lessons
WHERE lesson_id = ?;
SQL;
	$insertSQL = <<<SQL
INSERT INTO groups_for_lessons (lesson_id, group_id)
VALUES (?, ?);
SQL;

	$id = $endpoint->parseUuid($data['parent-id'])->getBytes();
	if(isset($data['group']))
	{
		foreach($data['group'] as $group)
		{
			$groups[] = $endpoint->parseUuid($group)->getBytes();
		}
	}

	$db = new OdymaterialyAPI\Database();
	$db->start_transaction();

	$db->prepare($deleteSQL);
	$db->bind_param('s', $id);
	$db->execute();

	if(isset($groups))
	{
		$db->prepare($insertSQL);
		foreach($groups as $group)
		{
			$db->bind_param('ss', $id, $group);
			$db->execute("lesson or group");
		}
	}
	$db->finish_transaction();
	return ['status' => 200];
};
$lessonGroupEndpoint->setUpdateMethod(new OdymaterialyAPI\Role('editor'), $updateLessonGroups);
