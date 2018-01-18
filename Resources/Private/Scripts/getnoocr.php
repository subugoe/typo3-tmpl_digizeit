<?php
$strOCRPath = '/storage/digizeit/gdzocr/';
$strDZPath = '/storage/digizeit/mets/';

$arrOCR = scandir($strOCRPath);
foreach ($arrOCR as $k => $v) {
	$arrOCR[$k] = trim($v);
}

$arrDZ = scandir($strDZPath);
foreach ($arrDZ as $k => $v) {
	$ppn = trim(str_replace('.xml', '', trim($v)));
	if (substr($ppn, 0, 3) != 'PPN' || strlen($ppn) <= 12) {
		unset($arrDZ[$k]);
	} else {
		$arrDZ[$k] = $ppn;
	}
}

$arr = array_values(array_diff($arrDZ, $arrOCR));

echo(json_encode($arr));
