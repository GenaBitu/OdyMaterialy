<?php
@_API_EXEC === 1 or die('Restricted access.');

require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/internal/AnonymousField.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/internal/Database.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/internal/Endpoint.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/internal/Field.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/internal/Lesson.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/internal/Role.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/internal/exceptions/InvalidArgumentTypeException.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/internal/exceptions/MissingArgumentException.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/internal/exceptions/NotFoundException.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/endpoints/lessonCompetenceEndpoint.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/endpoints/lessonFieldEndpoint.php');

use Ramsey\Uuid\Uuid;

$lessonEndpoint = new OdyMaterialyAPI\Endpoint('lesson');
$lessonEndpoint->addSubEndpoint('competence', $lessonCompetenceEndpoint);
$lessonEndpoint->addSubEndpoint('field', $lessonFieldEndpoint);

$listLessons = function($skautis, $data, $endpoint)
{
	$field_sql = <<<SQL
SELECT id, name
FROM fields;
SQL;
	$anonymous_sql = <<<SQL
SELECT lessons.id, lessons.name, lessons.version
FROM lessons
LEFT JOIN lessons_in_fields ON lessons.id = lessons_in_fields.lesson_id
WHERE lessons_in_fields.field_id IS NULL;
SQL;
	$lesson_sql = <<<SQL
SELECT lessons.id, lessons.name, lessons.version
FROM lessons
JOIN lessons_in_fields ON lessons.id = lessons_in_fields.lesson_id
WHERE lessons_in_fields.field_id = ?;
SQL;
	$competence_sql = <<<SQL
SELECT competences.id, competences.number
FROM competences
JOIN competences_for_lessons ON competences.id = competences_for_lessons.competence_id
WHERE competences_for_lessons.lesson_id = ?
ORDER BY competences.number;
SQL;

	// Select anonymous lessons
	$db = new OdymaterialyAPI\Database();
	$db->prepare($anonymous_sql);
	$db->execute();
	$lesson_id = '';
	$lesson_name = '';
	$lesson_version = '';
	$db->bind_result($lesson_id, $lesson_name, $lesson_version);
	$fields = [new OdymaterialyAPI\AnonymousField()];

	if($db->fetch())
	{
		do
		{
			// Create a new Lesson in the newly-created Field
			end($fields)->lessons[] = new OdyMaterialyAPI\Lesson($lesson_id, $lesson_name, $lesson_version);

			// Find out the competences this Lesson belongs to
			$db2 = new OdymaterialyAPI\Database();
			$db2->prepare($competence_sql);
			$db2->bind_param('s', $lesson_id);
			$db2->execute();
			$competence_id = '';
			$competence_number = '';
			$db2->bind_result($competence_id, $competence_number);
			end(end($fields)->lessons)->lowestCompetence = 0;
			if($db2->fetch())
			{
				end(end($fields)->lessons)->lowestCompetence = $competence_number;
				end(end($fields)->lessons)->competences[] = $competence_id;
			}
			else
			{
				end(end($fields)->lessons)->lowestCompetence = 0;
			}
			while($db2->fetch())
			{
				end(end($fields)->lessons)->competences[] = $competence_id;
			}
		}
		while($db->fetch());
	}

	// Select all the fields in the database
	$db->prepare($field_sql);
	$db->execute();
	$field_id = '';
	$field_name = '';
	$db->bind_result($field_id, $field_name);

	while($db->fetch())
	{
		$fields[] = new OdyMaterialyAPI\Field($field_id, $field_name); // Create a new field

		// Populate the newly-created Field with its lessons
		$db2 = new OdymaterialyAPI\Database();
		$db2->prepare($lesson_sql);
		$db2->bind_param('s', $field_id);
		$db2->execute();
		$lesson_id = '';
		$lesson_name = '';
		$lesson_version = '';
		$db2->bind_result($lesson_id, $lesson_name, $lesson_version);
		while($db2->fetch())
		{
			// Create a new Lesson in the newly-created Field
			end($fields)->lessons[] = new OdyMaterialyAPI\Lesson($lesson_id, $lesson_name, $lesson_version);

			// Find out the competences this Lesson belongs to
			$db3 = new OdymaterialyAPI\Database();
			$db3->prepare($competence_sql);
			$db3->bind_param('s', $lesson_id);
			$db3->execute();
			$competence_id = '';
			$competence_number = '';
			$db3->bind_result($competence_id, $competence_number);
			end(end($fields)->lessons)->lowestCompetence = 0;
			if($db3->fetch())
			{
				end(end($fields)->lessons)->lowestCompetence = $competence_number;
				end(end($fields)->lessons)->competences[] = $competence_id;
			}
			else
			{
				end(end($fields)->lessons)->lowestCompetence = 0;
			}
			while($db3->fetch())
			{
				end(end($fields)->lessons)->competences[] = $competence_id;
			}
		}

		// Sort the lessons in the newly-created Field - sorts by lowest competence low-to-high
		usort(end($fields)->lessons, "OdyMaterialyAPI\Lesson_cmp");
	}
	usort($fields, 'OdyMaterialyAPI\Field_cmp'); // Sort all the Fields by lowest competence in the Field low-to-high
	return ['status' => 200, 'response' => $fields];
};
$lessonEndpoint->setListMethod(new OdyMaterialyAPI\Role('guest'), $listLessons);

$getLesson = function($skautis, $data, $endpoint)
{
	$SQL = <<<SQL
SELECT body
FROM lessons
WHERE id = ?;
SQL;

	$id = $endpoint->parseUuid($data['id'])->getBytes();

	$db = new OdyMaterialyAPI\Database();
	$db->prepare($SQL);
	$db->bind_param('s', $id);
	$db->execute();
	$body = '';
	$db->bind_result($body);
	$db->fetch_require('lesson');
	return ['status' => 200, 'response' => $body];
};
$lessonEndpoint->setGetMethod(new OdyMaterialyAPI\Role('guest'), $getLesson);

$addLesson = function($skautis, $data, $endpoint)
{
	$SQL = <<<SQL
INSERT INTO lessons (id, name, body)
VALUES (?, ?, ?);
SQL;

	if(!isset($data['name']))
	{
		throw new OdyMaterialyAPI\MissingArgumentException(OdyMaterialyAPI\MissingArgumentException::POST, 'name');
	}
	$name = $endpoint->xss_sanitize($data['name']);
	$body = '';
	if(isset($data['body']))
	{
		$body = $data['body'];
	}
	$id = Uuid::uuid4()->getBytes();

	$db = new OdymaterialyAPI\Database();
	$db->prepare($SQL);
	$db->bind_param('sss', $id, $name, $body);
	$db->execute();
	return ['status' => 201];
};
$lessonEndpoint->setAddMethod(new OdymaterialyAPI\Role('editor'), $addLesson);

$updateLesson = function($skautis, $data, $endpoint)
{
	$copySQL = <<<SQL
INSERT INTO deleted_lessons (id, name, version, body)
SELECT id, name, version, body
FROM lessons
WHERE id = ?;
SQL;
	$selectSQL = <<<SQL
SELECT name, body
FROM lessons
WHERE id = ?;
SQL;
	$updateSQL = <<<SQL
UPDATE lessons
SET name = ?, version = version + 1, body = ?
WHERE id = ?
LIMIT 1;
SQL;

	$id = $endpoint->parseUuid($data['id'])->getBytes();
	if(isset($data['name']))
	{
		$name = $endpoint->xss_sanitize($data['name']);
	}
	if(isset($data['body']))
	{
		$body = $data['body'];
	}

	$db = new OdymaterialyAPI\Database();

	if(!isset($name) or !isset($body))
	{
		$db->prepare($selectSQL);
		$db->bind_param('s', $id);
		$db->execute();
		$origName = '';
		$origBody = '';
		$db->bind_result($origName, $origBody);
		if(!$db->fetch())
		{
			throw new OdymaterialyAPI\NotFoundException('lesson');
		}
		if(!isset($name))
		{
			$name = $origName;
		}
		if(!isset($body))
		{
			$body = $origBody;
		}
	}

	$db->prepare($copySQL);
	$db->bind_param('s', $id);
	$db->execute();

	$db->prepare($updateSQL);
	$db->bind_param('sss', $name, $body, $id);
	$db->execute();
	return ['status' => 200];
};
$lessonEndpoint->setUpdateMethod(new OdymaterialyAPI\Role('editor'), $updateLesson);

$deleteLesson = function($skautis, $data, $endpoint)
{
	$copySQL = <<<SQL
INSERT INTO deleted_lessons (id, name, version, body)
SELECT id, name, version, body
FROM lessons
WHERE id = ?;
SQL;
	$deleteFieldSQL = <<<SQL
DELETE FROM lessons_in_fields
WHERE lesson_id = ?;
SQL;
	$deleteCompetencesSQL = <<<SQL
DELETE FROM competences_for_lessons
WHERE lesson_id = ?;
SQL;

	$deleteSQL = <<<SQL
DELETE FROM lessons
WHERE id = ?;
SQL;

	$id = $endpoint->parseUuid($data['id'])->getBytes();

	$db = new OdymaterialyAPI\Database();
	$db->start_transaction();

	$db->prepare($copySQL);
	$db->bind_param('s', $id);
	$db->execute();

	$db->prepare($deleteFieldSQL);
	$db->bind_param('s', $id);
	$db->execute();

	$db->prepare($deleteCompetencesSQL);
	$db->bind_param('s', $id);
	$db->execute();

	$db->prepare($deleteSQL);
	$db->bind_param('s', $id);
	$db->execute();

	$db->finish_transaction();
	return ['status' => 200];
};
$lessonEndpoint->setDeleteMethod(new OdymaterialyAPI\Role('administrator'), $deleteLesson);
