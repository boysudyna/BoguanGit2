<?php
/*
* 统计boguan每月对账数据
* 写入到数据库程序
*/
include '../../Library/mysqli.class.php';
ini_set('display_errors', true);
error_reporting(E_ALL ^ E_NOTICE);

$dbConfig = array(
    'host'    => '127.0.0.1', 
    'port'  => '3306',
    'db'      => 'test', 
    'user' => 'root', 
    'pass'  => 'root', 
);


$db = ConnectMysqli::getIntance($dbConfig);
$fileName = $_GET['file'] ? $_GET['file'] : '201801';
$handle  = fopen ('./data/'.$fileName.'.txt', "r");
echo  '开始创建数据库 =====<br />';
$tableName = "t_phone_draft";

$hasSql = "SHOW TABLES LIKE '{$tableName}'";
$hasRet = $db->getRow($hasSql);
// var_dump($hasSql);die;
if ($hasRet) {
    // $sql = "TRUNCATE TABLE {$tableName}";
    // $db->query($sql);
} else {
    $createSql = <<<SQL
        CREATE TABLE {$tableName} (
            `pd_id` bigint(11) NOT NULL,
            `pd_type` int(11) NOT NULL,
            `pd_phone` bigint(11) NOT NULL,
            `pd_province` varchar(50) NOT NULL,
            `pd_city` varchar(50) NOT NULL,
            `pd_handset` varchar(50) NOT NULL,
            `pd_color` varchar(20) NOT NULL,
            `pd_imei` varchar(20) NOT NULL,
            `pd_price` int(11) NOT NULL,
            `pd_sdate` datetime NOT NULL,
            `pd_edate` datetime NOT NULL,
            `pd_name` int(11) NOT NULL,
            `pd_mtime` int(11) NOT NULL,
            `pd_channeltype` int(11) NOT NULL,
            `pd_storeid` int(11) NOT NULL,
            UNIQUE  `U_index` (`pd_phone`,`pd_sdate`,`pd_name`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='手机保障数据表汇总'
SQL;
    $db->query($createSql);
}

echo  '数据库创建完成 =====<br />';
echo  '开始执行写入 =====<br />';
$stime = microtime(true);
$i = $ri = 0;
$sql = "INSERT IGNORE INTO {$tableName} VALUES ";
while (!feof ($handle)) {
    $buffer  = fgets($handle, 4096);
    // 跳过第一条数据字段名
    if ($i == 0)  {
        $i ++;
        continue;
    }  

    $strings = trim($buffer);
    // 按 | 分割数据，最后一条总记录也要跳过
    $pos = strpos($strings, '|');
    if ($pos === false) {
        $i ++;
        continue;
    }

    $arr = explode('|', $strings);
    array_map(addslashes, $arr);
    $arrStr .= '(\'' . implode('\',\'', $arr) . '\'),';
    if ($i % 1000 == 0) {
        $sqlStr = substr($sql . $arrStr, 0, -1);
        $rr = $db->query($sqlStr);
        $arrStr = '';
    } 

    $i++;
}

if ($arrStr) {
    $sqlStr = substr($sql . $arrStr, 0, -1);
    $rr = $db->query($sqlStr);
    $arrStr = '';
}


fclose ($handle);
$etime = microtime(true);
$utime = $etime - $stime;
$i -= 2;
echo  "写入成功,总条数：{$i}, 耗时：{$utime}s =====<br />";
exit;

