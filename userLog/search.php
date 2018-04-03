<?php
/*
* 统计用户的模型匹配
* 展示到页面前端
*/

define('DEBUG', true);
set_time_limit(0);
include '../library.inc.php';

if($_GET['key'] != 'exec')
    exit("请确认后在访问！");

$dbConfig = array(
    'host'    => '127.0.0.1', 
    'port'  => '3306',
    'db'      => 'test', 
    'user' => 'root', 
    'pass'  => 'root', 
);

$db = ConnectMysqli::getIntance($dbConfig);

$showArr = array(
	'入网年龄>=10年的正常数 ：异常数 ',
	'入网年龄10> x >=5年的正常数 ：异常数 ',
	'入网年龄5 > x 年的正常数 ：异常数 ',
	'VIP等级 -1~7级的正常数 ：异常数',
	'ARPU >= 500 的正常数 ：异常数',
	'ARPU 500 > x >= 300 的正常数 ：异常数',
	'ARPU 300 > x >= 100 的正常数 ：异常数',
	'ARPU 100 > x >= 50 的正常数 ：异常数',
	'ARPU 50 > x 的正常数 ：异常数',
);

$arpuArr = array(50, 100, 300, 500);

$tableName = 't_user_phone';
$sql = "select count(*) as s from {$tableName} where ";
$sqlArr = array(
	"up_open_date <= '2008-03-31' and up_abnormal = 0", // 10年以上
	"up_open_date <= '2008-03-31' and up_abnormal = 1",
	"up_open_date <= '2013-03-31' and up_open_date >= '2008-04-01' and up_abnormal = 0", // 5-10年 
	"up_open_date <= '2013-03-31' and up_open_date >= '2008-04-01' and up_abnormal = 1",
	"up_open_date >= '2013-04-01' and up_abnormal = 0", // 5年以下
	"up_open_date >= '2013-04-01' and up_abnormal = 1",
);

$i = 0;
foreach ($sqlArr as $key => $condition) {
	$sqlStr = $sql . $condition;
	$countNum  = $db->getRow($sqlStr);
	$nums = $countNum['s'];
	if($key % 2 == 0) {
		$echoStr .= $showArr[$i] . $nums;
		$i++;
	} else
		$echoStr .= ' : ' . $nums .'<br />';
}

echo $echoStr;

// vip等级显示
$str = 'VIP等级 %d 级的正常数 ：异常数 ';
for($i = -1; $i <= 7; $i++) {
	$sqlStr = $sql . "up_vip = {$i} and up_abnormal = 0";
	$sqlStr2 = $sql . "up_vip = {$i} and up_abnormal = 1";
	$countNum  = $db->getRow($sqlStr);
	$countNum2  = $db->getRow($sqlStr2);
	echo sprintf($str, $i) . $countNum['s'] .' : '.$countNum2['s'] .'<br />';
}

// ARPU计算显示
$str = 'ARPU %d > x >= %d 的正常数 ：异常数 ';
foreach($arpuArr as $key => $value) {
	$prev = !$arpuArr[$key-1] ? '0.00' : $arpuArr[$key-1];
	$last = !$arpuArr[$key+1] ? '' : $arpuArr[$key+1];
	$cur = $value;
	if($prev) {
		$sqlStr = $sql . "up_ARPU >= {$prev} and up_ARPU < {$cur} and up_abnormal = 0";
		$sqlStr2 = $sql . "up_ARPU >= {$prev} and up_ARPU < {$cur} and up_abnormal = 1";
		$countNum  = $db->getRow($sqlStr);
		$countNum2  = $db->getRow($sqlStr2);
		echo sprintf($str, $cur, $prev) . $countNum['s'] .' : '.$countNum2['s'] .'<br />';
	}

	if(! $last) {
		$sqlStr = $sql . "up_ARPU >= {$cur} and up_abnormal = 0";
		$sqlStr2 = $sql . "up_ARPU >= {$cur} and up_abnormal = 1";
		$countNum  = $db->getRow($sqlStr);
		$countNum2  = $db->getRow($sqlStr2);
		echo sprintf($str, '99999', $cur) . $countNum['s'] .' : '.$countNum2['s'] .'<br />';
	}
}

exit;