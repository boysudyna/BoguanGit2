<?php
/*
* 统计用户的移动消费数据
* 写入到数据库程序
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
$fileName = $_GET['file'] ? $_GET['file'] : '1';
$handle  = fopen ('./data/'.$fileName.'.tsv', "r");
echo  '开始创建数据库 =====<br />';
$tableName = "t_user_phone";

$hasSql = "SHOW TABLES LIKE '{$tableName}'";
$hasRet = $db->getRow($hasSql);
if ($hasRet) {
    // $sql = "TRUNCATE TABLE {$tableName}";
    // $db->query($sql);
} else {
    $createSql = <<<SQL
        CREATE TABLE {$tableName} (
            `up_id` int(11) NOT NULL AUTO_INCREMENT,
            `up_phone` bigint(11) NOT NULL,
            `up_open_date` date NOT NULL,
            `up_vip` tinyint(2) NOT NULL,
            `up_meal` varchar(50) NOT NULL,
            `up_MOU` decimal(10,2) NOT NULL,
            `up_DOU` decimal(10,2) NOT NULL,
            `up_ARPU` decimal(10,2) NOT NULL,
            `up_used_type` varchar(50) NOT NULL,
            `up_used_month` smallint(6) NOT NULL,
            `up_is_minimum` tinyint(2) NOT NULL,
            PRIMARY KEY (`up_id`),
            KEY `up_phone` (`up_phone`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户手机数据'
SQL;
    $db->query($createSql);
}

echo  '数据库创建完成 =====<br />';
echo  '开始执行写入 =====<br />';
$stime = microtime(true);
$i = $ri = 0;
$sql = "INSERT INTO {$tableName} VALUES ";
while (!feof ($handle)) {
    $data = array();
    $buffer  = fgets($handle, 4096);
    // 跳过第一条数据字段名
    if ($i == 0)  {
        $i ++;
        continue;
    }  

    // $strings = trim($buffer);
    $strings = $buffer;
    $strings = iconv("GB2312", "UTF-8//IGNORE", $strings);
    $arr = explode("\t", $strings);
    array_map(addslashes, $arr);
    $data[] = '';    
    $data[] = $arr[0];    
    $data[] = $arr[1];    
    $data[] = $arr[2];    
    $data[] = $arr[3];    
    $data[] = $arr[4];    
    $data[] = $arr[5];    
    $data[] = $arr[6];    
    $data[] = $arr[8] ? $arr[8] : '';    
    $data[] = (int)$arr[9];    
    $data[] = (int)$arr[12];   
    if(! $arr[0]) 
        continue;

    $arrStr .= '(\'' . implode('\',\'', $data) . '\'),';
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
$i -= 1;
echo  "写入成功,总条数：{$i}, 耗时：{$utime}s =====<br />";
exit;

