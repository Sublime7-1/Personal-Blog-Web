<?php
require_once( './PHPExcel/IOFactory.php');
$con = mysql_connect("rdsaamqrqz3y7fn.mysql.rds.aliyuncs.com", "xkproject", "xk2Project");
if (!$con) {
    die('Could not connect: ' . mysql_error());
}

mysql_select_db("sq_game", $con);
mysql_query('set names utf8');

$filePath = './datecount.xlsx'; //excel 文件名 
$objReader = new PHPExcel_Reader_Excel2007();  //具体查看（Documentation/Examples/Reader/exampleReader01.php）
$objPHPExcel = $objReader->load($filePath);
$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
$insql = 'insert into datecount (`dateline`,`usernum`,`hours`,`clicknum`,`shareUnum`,`shareFnum`,`friendClick`,`timelineClick`) VALUES ';
//遍历数组 $sheetData
//如果有标题 先删除 unset($sheetData[1]);
foreach($sheetData as $k => $data){
	if(!empty($data["H"])){
		$dataB = $data["B"];
		$dataC = $data["C"];
		$dataK = $data["K"];
		$dataL = $data["L"];
		$dataM = $data["M"];
		$dataN = $data["N"];
		$dataO = $data["O"];
		$dataP = $data["P"];
		$insql .= "('$dataB','$dataC','$dataK','$dataL','$dataM','$dataN','$dataO','$dataP'),";
		//  $insql .= '('.$data['A'].','.$data['B'].','.$data['C'].'),';
		//一次插入100条数据  减少数据库压力
		//echo '<br>';
		if(($k+1 / 100) == 1){
			$insql = rtrim($insql,',').';'; //将最后的逗号替换成分好
			//插入数据库 并且重置 字符串 $insql
			//或者保存到文件中 利用source 命令插入数据库
			echo 1;
		}
		//var_dump($data);
	}
	
	
}
$insql = substr($insql,0,-1); //去掉最后一个逗号
var_dump($insql);
$res = mysql_query($insql);
var_dump($res);
?>