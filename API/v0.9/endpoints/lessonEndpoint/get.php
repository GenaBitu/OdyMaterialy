<?php declare(strict_types=1);
@_API_EXEC === 1 or die('Restricted access.');

require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/internal/Database.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/internal/exceptions/RoleException.php');

$getLesson = function(Skautis\Skautis $skautis, array $data, OdyMaterialyAPI\Endpoint $endpoint) : array
{
	$SQL = <<<SQL
SELECT body
FROM lessons
WHERE id = ?;
SQL;

	$id = $endpoint->parseUuid($data['id']);

	if(!checkLessonGroup($id, true))
	{
		throw new OdyMaterialyAPI\RoleException();
	}

	$id = $id->getBytes();

	$db = new OdyMaterialyAPI\Database();
	$db->prepare($SQL);
	$db->bind_param('s', $id);
	$db->execute();
	$body = '';
	$db->bind_result($body);
	$db->fetch_require('lesson');
	return ['status' => 200, 'response' => $body];
};
