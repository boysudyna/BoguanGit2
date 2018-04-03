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
$tableName = "t_phone_detail_{$fileName}";

$hasSql = "SHOW TABLES LIKE '{$tableName}'";
$hasRet = $db->getRow($hasSql);
// var_dump($hasSql);die;
if ($hasRet) {
    $sql = "TRUNCATE TABLE {$tableName}";
    $db->query($sql);
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
            `pd_storeid` int(11) NOT NULL
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='每月数据表'
SQL;
    $db->query($createSql);
}

echo  '数据库创建完成 =====<br />';
echo  '开始执行写入 =====<br />';
$stime = microtime(true);
$i = 0;
$sql = "INSERT INTO {$tableName} VALUES ";
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
        $db->query($sqlStr);
        $arrStr = '';
    } 

    $i++;
}

if ($arrStr) {
    $sqlStr = substr($sql . $arrStr, 0, -1);
    $db->query($sqlStr);
    $arrStr = '';
}


fclose ($handle);
$etime = microtime(true);
$utime = $etime - $stime;
$i -= 2;
echo  "写入成功,总条数：{$i}, 耗时：{$utime}s =====<br />";
exit;

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

