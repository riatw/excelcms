<?php

// 設定
$filename = "data.xlsx";

require __DIR__ . '/vendor/autoload.php';

// PHP Excelの読み込み
$reader = PHPExcel_IOFactory::createReader( 'Excel2007' );

// Twigの読み込み
$loader = new Twig_Loader_Filesystem('tmpl');
$twig = new Twig_Environment($loader);

if($reader){
	//Excelファイルの読み込み
	//必ず最初のシートにデータを保存すること
	$excel = $reader->load($filename);
	$excel->setActiveSheetIndex(0);
	$sheet = $excel->getActiveSheet();

	//配列で返す
	$obj = $sheet->toArray( null, true, true, true );
}

$keyarray=array();
$dataarray=array();

foreach($obj as $rowindex=>$rowdata){
	if($rowindex == '1'){
		//1行目にカラム名として連想配列のキーが入っている
		foreach($rowdata as $key=>$value){
			$keyarray[$key]=$value;
		}
	} else {
		//2行目以降は、1行目で求めたカラム名を連想配列のキーとする
		foreach($rowdata as $key=>$value){
			$dataarray[$rowindex-1][$keyarray[$key]] = $value;
		}
	}
}
$dataarray = array_values($dataarray);

if ( $_GET['__mode'] == "view" ) {
	// detail
	$entry = null;
	$current_id = $_GET["id"];

	for ( $i = 0; $i < count( $dataarray ); $i++ ) {
		if ( $dataarray[$i]["id"] == $current_id ) {
			$entry = $dataarray[$i];
		}
	}

	$template = $twig->loadTemplate('detail.twig');
	$data = array(
		'title' => 'Index',
		'entry' => $entry
	);
	echo $template->render($data);
}
else if ( $_GET['__mode'] == "api" ) {
	echo json_encode($dataarray);
}
else {
	// index
	$template = $twig->loadTemplate('index.twig');
	$data = array(
		'title' => 'Index',
		'entries' => $dataarray
	);
	echo $template->render($data);
}

?>