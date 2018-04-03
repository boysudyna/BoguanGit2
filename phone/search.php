<?php
/*
* 统计boguan每月对账数据
* 计算核对数据
* Add:统计汇总到数据库并关闭默认写入文件
*/
define('DEBUG', true);
set_time_limit(0);
include '../library.inc.php';
date_default_timezone_set('Asia/Shanghai');

$dbConfig = array(
    'host'    => '127.0.0.1', 
    'port'  => '3306',
    'db'      => 'test', 
    'user' => 'root', 
    'pass'  => 'root', 
);

$inpFileTag = $_GET['input'] ? $_GET['input'] : 0;
$currMonth = date('Ym');

$monTag = $fileName = $_GET['file'] ? $_GET['file'] : '201801';

$putFileName = 'count_'.$fileName.'.txt';
// 重置表内容
if($inpFileTag)
    $fopen = file_put_contents($putFileName, '');

// 表头
$fileStr .= "电话\t运营商\t订购开始日期\t订购结束日期\t套餐\t天数\t价格".PHP_EOL;
if($inpFileTag)
    $fopen = file_put_contents($putFileName, $fileStr, FILE_APPEND);

$tableName = "t_phone_detail";
$searchName = "t_phone_draft";
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
if (! $hasRet) {
    $createSql = <<<SQL
        CREATE TABLE {$tableName} (
        `m_id` int(11) NOT NULL AUTO_INCREMENT,
        `m_phone` bigint(20) NOT NULL,
        `m_city` varchar(50) NOT NULL,
        `m_sdate` datetime NOT NULL,
        `m_edate` datetime NOT NULL,
        `m_name` int(11) NOT NULL,
        `m_days` int(11) NOT NULL,
        `m_money` decimal(10,2) NOT NULL,
        `m_tag` char(6) NOT NULL,
        `m_insdate` char(6) NOT NULL,
        PRIMARY KEY (`m_id`),
        UNIQUE  `U_index` (`m_phone`,`m_sdate`,`m_name`,`m_tag`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='统计标记月表'
SQL;
    $db->query($createSql);
}

$stime = microtime(true);
echo  '开始读取数据库并计算 =====<br />';
$condSql = "pd_sdate<='{$monthLast}' AND pd_edate>='{$monthFir}'";
$insSql = "INSERT IGNORE INTO {$tableName} VALUES ";
$per = 1000;
$offset = 0;
$sql = "SELECT COUNT(*) as S FROM {$searchName} WHERE {$condSql}";
$countNum = $db->getRow($sql);
$countNum = $countNum['S'];
$totalNum += $countNum;
do{
    $sql = "SELECT pd_phone,pd_city,pd_sdate,pd_edate,pd_name
            FROM {$searchName} 
            WHERE {$condSql}
            LIMIT {$offset}, {$per}";
    $retArr = $db->getAll($sql);
    $offset += $per;
    if (!$retArr) 
        continue;

    $insStr = '';
    $fileStr = "";
    foreach ($retArr as $key => $val) {
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

        $needPrice = $needPrice >= $monthPrice ? $monthPrice : $needPrice;
        $insStr .= "('',{$val['pd_phone']},'{$val['pd_city']}','{$val['pd_sdate']}',
                    '{$val['pd_edate']}',{$val['pd_name']},{$days},{$needPrice},'{$monTag}',{$currMonth}),";  
        $fileStr .= "{$val['pd_phone']}\t{$val['pd_city']}\t{$val['pd_sdate']}\t{$val['pd_edate']}\t{$val['pd_name']}\t{$days}\t{$needPrice}".PHP_EOL;
        
        $needTotal += $needPrice;
    }

    $sqlStr = substr($insSql . $insStr, 0, -1);
    $db->query($sqlStr);
    if($inpFileTag)
        $fopen = file_put_contents($putFileName, $fileStr, FILE_APPEND);
}while($offset < $countNum);

$needTotal =  round($needTotal, 2);
$fileStr .= "\t\t\t\t\t\t\t应收金额：{$needTotal}".PHP_EOL;
if($inpFileTag)
    $fopen = file_put_contents($putFileName, $fileStr, FILE_APPEND);

// 汇总统计到数据库中
$countTable = "t_phone_detail_count";
$iSql = "INSERT INTO {$countTable} VALUES ('','{$monTag}','{$needTotal}','{$currMonth}')";
$db->query($iSql);

$etime = microtime(true);
$utime = $etime - $stime;
echo  "写入成功,总条数：{$totalNum}, 耗时：{$utime}s =====<br />";
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