<?php
/*
* 统计boguan每月对账数据
* 计算核对数据
*/
include '../../Library/mysqli.class.php';
date_default_timezone_set('Asia/Shanghai');
ini_set('display_errors', true);
error_reporting(E_ALL ^ E_NOTICE);

$dbConfig = array(
    'host'    => '127.0.0.1', 
    'port'  => '3306',
    'db'      => 'test', 
    'user' => 'root', 
    'pass'  => 'root', 
);

$fileName = $_GET['file'] ? $_GET['file'] : '201801';
$putFileName = 'count_'.$fileName.'.txt';
// 重置表内容
$fopen = file_put_contents($putFileName, '');
// 表头
$fileStr .= "电话\t运营商\t订购开始日期\t订购结束日期\t套餐\t天数\t价格".PHP_EOL;
$fopen = file_put_contents($putFileName, $fileStr, FILE_APPEND);

$tableName = "t_phone_count_{$fileName}";
$searchName = "t_phone_detail_{$fileName}";
$y = substr($fileName, 0, 4);
$m = substr($fileName, 4, 2);
// 当月的数据实际为统计上个月
$y = date('Y', strtotime("{$y}-{$m}-01 00:00 -1 months"));
$m = date('m', strtotime("{$y}-{$m}-01 00:00 -1 months"));
$priceConfig = array(
    '1000260007' => 4,
    '1000260008' => 9,
    '1000260009' => 15,
    '1000260010' => 38,
    '1000260011' => 29,
    '1000260012' => 29,
);

$monthFir = $y.'-'.$m.'-01'.' 00:00:00';
$monthDays = date('t', mktime(0,0,0,$m,01,$y));
$monthLast = $y.'-'.$m.'-'.$monthDays.' 00:00:00';

$db = ConnectMysqli::getIntance($dbConfig);
$hasSql = "SHOW TABLES LIKE '{$tableName}'";
$hasRet = $db->getRow($hasSql);
// var_dump($hasSql);die;
if ($hasRet) {
    $sql = "TRUNCATE TABLE {$tableName}";
    $db->query($sql);
} else {
    $createSql = <<<SQL
        CREATE TABLE {$tableName} (
        `pc_id` int(11) NOT NULL AUTO_INCREMENT,
        `pc_phone` bigint(20) NOT NULL,
        `pc_city` varchar(50) NOT NULL,
        `pc_sdate` datetime NOT NULL,
        `pc_edate` datetime NOT NULL,
        `pc_name` int(11) NOT NULL,
        `pc_days` int(11) NOT NULL,
        `pc_money` decimal(10,2) NOT NULL,
        PRIMARY KEY (`pc_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='月份统计表'
SQL;
    $db->query($createSql);
}

echo  '开始读取数据库并计算 =====<br />';
$stime = microtime(true);
$per = 1000;
$sql = "SELECT COUNT(*) as S FROM {$searchName}";
$countNum = $db->getRow($sql);
$countNum = $countNum['S'];

$offset = 0;
$curTotal = 0;
$prevTotal = 0;
$insSql = "INSERT INTO {$tableName} VALUES ";
do {
    $sql = "SELECT pd_phone,pd_city,pd_sdate,pd_edate,pd_name
            FROM {$searchName} 
            LIMIT {$offset}, {$per}";
    $retArr = $db->getAll($sql);
    $offset += $per;
    if (!$retArr) 
        continue;

    $insStr = '';
    $fileStr = '';
    foreach ($retArr as $key => $val) {
        $curTag = 1;
        // 月费
        $monthPrice = $priceConfig[$val['pd_name']];
        // 天费
        $dayPrice = ceil(($monthPrice/$monthDays)*100)/100; // 小数点后两位进一取法
        // 整月
        if ($val['pd_sdate'] <= $monthFir && $val['pd_edate'] >= $monthLast) {
            $days = $monthDays;
            $needPrice = $monthPrice;
        }
        // 前月开通，当月结束
        if ($val['pd_sdate'] <= $monthFir && $val['pd_edate'] < $monthLast && $val['pd_edate'] > $monthFir) {
            $days = diff_days($monthFir, $val['pd_edate']);
            $needPrice = $dayPrice * $days;
        }
        // 当月开通，后月结束
        if ($val['pd_sdate'] > $monthFir && $val['pd_edate'] >= $monthLast) {
            $days = diff_days($val['pd_sdate'], $monthLast); 
            $needPrice = $dayPrice * $days;   
        }
        // 当月开通，当月结束    
        if ($val['pd_sdate'] > $monthFir && $val['pd_edate'] < $monthLast) {
            $days = diff_days($val['pd_sdate'], $val['pd_edate']);
            $needPrice = $dayPrice * $days;
        }

        // 前月开通，前月结束的-丢失数据
        if ($val['pd_sdate'] <= $monthFir && $val['pd_edate'] < $monthFir) {
            $curTag = 0;
            // pd_date 不等于1号的,取本月, 否则取前月
            $curM = date('d', strtotime($val['pd_edate']));
            if ($curM != '01') {
                $y = date('Y', strtotime($val['pd_edate']));
                $m = date('m', strtotime($val['pd_edate']));
                $curDays = date('t', mktime(0,0,0,$m,01,$y));
                $curMonthFir = $y.'-'.$m.'-01'.' 00:00:00';
                $sdate = $val['pd_sdate'] <= $curMonthFir ? $curMonthFir : $val['pd_sdate'];
                $days = diff_days($sdate, $val['pd_edate']);
                $dayPrice = ceil(($monthPrice/$curDays)*100)/100; // 小数点后两位进一取法
            } else {
                $y = date('Y', strtotime("{$val['pd_edate']} -1 months"));
                $m = date('m', strtotime("{$val['pd_edate']} -1 months"));
                $curDays = date('t', mktime(0,0,0,$m,01,$y));
                $curMonthFir = $y.'-'.$m.'-01'.' 00:00:00';
                $curMonthLast = $y.'-'.$m.'-'.$curDays.' 00:00:00';
                $sdate = $val['pd_sdate'] <= $curMonthFir ? $curMonthFir : $val['pd_sdate'];
                $days = diff_days($sdate, $curMonthLast);
                $dayPrice = ceil(($monthPrice/$curDays)*100)/100; // 小数点后两位进一取法
            }

            $needPrice = $dayPrice * $days;
        }

        $needPrice = $needPrice >= $monthPrice ? $monthPrice : $needPrice;
        $insStr .= "('',{$val['pd_phone']},'{$val['pd_city']}','{$val['pd_sdate']}',
                    '{$val['pd_edate']}',{$val['pd_name']},{$days},{$needPrice}),";  
        $fileStr .= "{$val['pd_phone']}\t{$val['pd_city']}\t{$val['pd_sdate']}\t{$val['pd_edate']}\t{$val['pd_name']}\t{$days}\t{$needPrice}".PHP_EOL;

        if ($curTag)
            $curTotal += $needPrice;
        else
            $prevTotal += $needPrice;

    }

    $sqlStr = substr($insSql . $insStr, 0, -1);
    $db->query($sqlStr);
    $fopen = file_put_contents($putFileName, $fileStr, FILE_APPEND);
} while ( $offset < $countNum);

$prevTotal = round($prevTotal, 2);
$curTotal = round($curTotal, 2);
$needTotal = $prevTotal + $curTotal;
$fileStr .= "\t\t\t\t欠收金额：{$prevTotal}\t应收金额：{$curTotal}\t总金额：{$needTotal}".PHP_EOL;
$fopen = file_put_contents($putFileName, $fileStr, FILE_APPEND);

$etime = microtime(true);
$utime = $etime - $stime;
echo  "写入成功,总条数：{$countNum}, 耗时：{$utime}s =====<br />";
exit;

function diff_days($sdate, $edate) {
    $sTime = strtotime(substr($sdate,0,10));
    $eTime = strtotime(substr($edate,0,10));
    $days = 0;
    if($eTime >= $sTime) {
        $days = ($eTime - $sTime)/86400;
        $days += 1;
    }    

    return $days;
}

/*$mysqli = @new mysqli($dbConfig['host'], $dbConfig['db_user'], $dbConfig['db_pwd']);
if ($mysqli->connect_errno) {
    die("could not connect to the database:\n" . $mysqli->connect_error);//诊断连接错误
}

$mysqli->query("set names 'utf8';");//编码转化
$select_db = $mysqli->select_db($dbConfig['db']);
if (!$select_db) {
    die("could not connect to the db:\n" .  $mysqli->error);
}

$sql = "select * from t_phone_detail limit 1";
$res = $mysqli->query($sql);
if (!$res) {
    die("sql error:\n" . $mysqli->error);
}

while ($row = $res->fetch_assoc()) {
        var_dump($row);
    }

$res->free();
$mysqli->close();*/

