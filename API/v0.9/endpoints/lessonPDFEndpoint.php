<?php declare(strict_types=1);
@_API_EXEC === 1 or die('Restricted access.');

require_once($_SERVER['DOCUMENT_ROOT'] . '/settings.php');
require_once($BASEPATH . '/vendor/autoload.php');
require_once($BASEPATH . '/v0.9/internal/Database.php');
require_once($BASEPATH . '/v0.9/internal/Endpoint.php');
require_once($BASEPATH . '/v0.9/internal/Helper.php');
require_once($BASEPATH . '/v0.9/internal/OdyMarkdown/OdyMarkdown.php');

use Ramsey\Uuid\Uuid;

$lessonPDFEndpoint = new HandbookAPI\Endpoint();

$getLessonPDF = function(Skautis\Skautis $skautis, array $data, HandbookAPI\Endpoint $endpoint) use ($BASEPATH) : void
{
	$SQL = <<<SQL
SELECT name
FROM lessons
WHERE id = :id;
SQL;

	$id = HandbookAPI\Helper::parseUuid($data['parent-id'], 'lesson')->getBytes();

	$db = new HandbookAPI\Database();
	$db->prepare($SQL);
	$db->bindParam(':id', $id, PDO::PARAM_STR);
	$db->execute();
	$name = '';
	$db->bindColumn('name', $name);
	$db->fetchRequire('lesson');
	unset($db);

	$md = $endpoint->getParent()->call('GET', new HandbookAPI\Role('guest'), ['id' => $data['parent-id']])['response'];

	$html = '<body><h1>' . strval($name) . '</h1>';
	$parser = new OdyMarkdown\OdyMarkdown();
	$html .= $parser->parse($md);

	$html .= '</body>';

	$mpdf = new \Mpdf\Mpdf([
		'fontDir' => [$BASEPATH . '/v0.9/internal/OdyMarkdown/fonts/'],
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

	$qrRenderer = new \BaconQrCode\Renderer\Image\Png();
	$qrRenderer->setHeight(90);
	$qrRenderer->setWidth(90);
	$qrWriter = new \BaconQrCode\Writer($qrRenderer);

	$mpdf->DefHTMLHeaderByName('OddHeaderFirst', '<img class="QRheader" src="data:image/png;base64,' . base64_encode($qrWriter->writeString('https://odymaterialy.skauting.cz/lesson/' . OdyMaterialyAPI\Helper::parseUuid($data['parent-id'], 'lesson')->toString())) . '">');
	$mpdf->DefHTMLHeaderByName('OddHeader', '<div class="oddHeaderRight">' . strval($name) . '</div>');
	$mpdf->DefHTMLFooterByName('OddFooter', '<div class="oddFooterLeft">...jsme na jedné lodi</div><img class="oddFooterRight" src="' . $BASEPATH . '/v0.9/internal/OdyMarkdown/images/logo.svg' . '">');
	$mpdf->DefHTMLFooterByName('EvenFooter', '<div class="evenFooterLeft">Odyssea ' . date('Y') . '</div><img class="evenFooterRight" src="' . $BASEPATH . '/v0.9/internal/OdyMarkdown/images/ovce.svg' . '">');

	$mpdf->SetHTMLHeaderByName('OddHeaderFirst', 'O');
	$mpdf->SetHTMLFooterByName('OddFooter', 'O');
	$mpdf->SetHTMLFooterByName('EvenFooter', 'E');

	$mpdf->WriteHTML('', 2);
	$mpdf->SetHTMLHeaderByName('OddHeader', 'O');

	$mpdf->WriteHTML(file_get_contents($BASEPATH . '/v0.9/internal/OdyMarkdown/main.css'), 1);
	$mpdf->WriteHTML($html, 2);

	header('content-type:application/pdf; charset=utf-8');
	$mpdf->Output();
};
$lessonPDFEndpoint->setListMethod(new HandbookAPI\Role('guest'), $getLessonPDF);
