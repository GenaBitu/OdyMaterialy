<?php
@_API_EXEC === 1 or die('Restricted access.');

require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/internal/Database.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/internal/Endpoint.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/internal/OdyMarkdown/OdyMarkdown.php');

use Ramsey\Uuid\Uuid;

$lessonPDFEndpoint = new OdyMaterialyAPI\Endpoint('latex');

$getLessonLatex = function($skautis, $data, $endpoint)
{
	$SQL = <<<SQL
SELECT name
FROM lessons
WHERE id = ?;
SQL;

	$id = $endpoint->parseUuid($data['parent-id'])->getBytes();

	$db = new OdymaterialyAPI\Database();
	$db->prepare($SQL);
	$db->bind_param('s', $id);
	$db->execute();
	$name = '';
	$db->bind_result($name);
	$db->fetch_require('lesson');
	unset($db);

	$md = $endpoint->getParent()->call('GET', ['id' => $data['parent-id']])['response'];

	$html = '<body><h1>' . $name . '</h1>';
	$parser = new OdyMarkdown\OdyMarkdown();
	$html .= $parser->parse($md);

	$html .= '</body>';

	$mpdf = new \Mpdf\Mpdf([
		'fontDir' => [$_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/internal/OdyMarkdown/fonts/'],
		'fontdata' => [
			'odymarathon' => [
				'R' => 'OdyMarathon-Regular.ttf'
			],
			'themix' => [
				'R' => 'TheMixC5-4_SemiLight.ttf',
				'I' => 'TheMixC5-4iSemiLightIta.ttf',
				'B' => 'TheMixC5-7_Bold.ttf',
				'BI' => 'TheMixC5-7iBoldItalic.ttf',
				'useOTL' => 0xFF,
				'useKashida' => 75,
			]
		],
		'default_font_size' => 8,
		'default_font' => 'themix',
		'format' => 'A5',
		'mirrorMargins' => true,
		'margin_top' => 12.5,
		'margin_left' => 19.5,
		'margin_right' => 12.25
	]);

	$mpdf->DefHTMLHeaderByName('OddHeader', '<div class="oddHeaderRight">' . $name . '</div>');
	$mpdf->DefHTMLFooterByName('OddFooter', '<div class="oddFooterLeft">...jsme na jedné lodi</div><img class="oddFooterRight" src="' . $_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/internal/OdyMarkdown/images/logo.svg' . '">');
	$mpdf->DefHTMLFooterByName('EvenFooter', '<div class="evenFooterLeft">Odyssea ' . date('Y') . '</div><img class="evenFooterRight" src="' . $_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/internal/OdyMarkdown/images/ovce.svg' . '">');
	$mpdf->SetHTMLHeaderByName('OddHeader', 'O');
	$mpdf->SetHTMLFooterByName('OddFooter', 'O');
	$mpdf->SetHTMLFooterByName('EvenFooter', 'E');

	$mpdf->WriteHTML(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/API/v0.9/internal/OdyMarkdown/main.css'), 1);
	$mpdf->WriteHTML($html, 2);

	header('content-type:application/pdf; charset=utf-8');
	$mpdf->Output();
};
$lessonPDFEndpoint->setListMethod(new OdymaterialyAPI\Role('guest'), $getLessonLatex);
