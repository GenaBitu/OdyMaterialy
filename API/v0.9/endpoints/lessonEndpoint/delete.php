<?php declare(strict_types=1);
@_API_EXEC === 1 or die('Restricted access.');

require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/internal/Database.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/internal/Helper.php');

$deleteLesson = function(Skautis\Skautis $skautis, array $data, OdyMaterialyAPI\Endpoint $endpoint) : array
{
	$copySQL = <<<SQL
INSERT INTO deleted_lessons (id, name, version, body)
SELECT id, name, version, body
FROM lessons
WHERE id = :id;
SQL;
	$deleteFieldSQL = <<<SQL
DELETE FROM lessons_in_fields
WHERE lesson_id = :lesson_id;
SQL;
	$deleteCompetencesSQL = <<<SQL
DELETE FROM competences_for_lessons
WHERE lesson_id = :lesson_id;
SQL;
	$deleteSQL = <<<SQL
DELETE FROM lessons
WHERE id = :id;
SQL;
	$countSQL = <<<SQL
SELECT ROW_COUNT();
SQL;

	$id = OdyMaterialyAPI\Helper::parseUuid($data['id'], 'lesson')->getBytes();

	$db = new OdyMaterialyAPI\Database();
	$db->start_transaction();

	$db->prepare($copySQL);
	$db->bindParam(':id', $id);
	$db->execute();

	$db->prepare($deleteFieldSQL);
	$db->bindParam(':lesson_id', $id);
	$db->execute();

	$db->prepare($deleteCompetencesSQL);
	$db->bindParam(':lesson_id', $id);
	$db->execute();

	$db->prepare($deleteSQL);
	$db->bindParam(':id', $id);
	$db->execute();

	$db->prepare($countSQL);
	$db->execute();
	$count = 0;
	$db->bind_result($count);
	$db->fetch_require('lesson');
	if($count != 1)
	{
		throw new OdyMaterialyAPI\NotFoundException("lesson");
	}

	$db->finish_transaction();
	return ['status' => 200];
};
