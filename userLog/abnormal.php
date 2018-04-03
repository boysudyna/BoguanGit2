<?php
/*
* 更新手机使用异常的用户
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
$fileName = $_GET['file'] ? $_GET['file'] : 'abnormal';
$handle  = fopen ('./data/'.$fileName.'.tsv', "r");
$tablePhone = "t_user_phone";
$tablePhoneLog = "t_user_phonelog";

echo  '开始执行写入 =====<br />';
$stime = microtime(true);
$i = 0;
$upStr = '';

$sql1 = "UPDATE {$tablePhone} SET up_abnormal = 1 WHERE up_phone IN({1})";
$sql2 = "UPDATE {$tablePhoneLog} SET up_abnormal = 1 WHERE up_phone IN({1})";
while (!feof ($handle)) {
    $buffer = fgets($handle, 1024);
    $phone = trim($buffer);
    if(!$phone)
        continue;
    $i++;
    $upStr .= $upStr ? ','.$phone : $phone;
    if($i % 500 == 0){
        $sqlStr1 = strtr($sql1, array('{1}' => $upStr));
        $rr = $db->query($sqlStr1);
        $sqlStr2 = strtr($sql2, array('{1}' => $upStr));
        $rr = $db->query($sqlStr2);
        $upStr = '';
    }
}

if ($upStr) {
    $sqlStr1 = strtr($sql1, array('{1}' => $upStr));
    $rr = $db->query($sqlStr1);
    $sqlStr2 = strtr($sql2, array('{1}' => $upStr));
    $rr = $db->query($sqlStr2);
    $upStr = '';
}

fclose ($handle);
$etime = microtime(true);
$utime = $etime - $stime;
$i -= 1;
echo  "写入成功,总条数：{$i}, 耗时：{$utime}s =====<br />";

echo "输出执行异常数据拉取语句<br />";
$sql = "SELECT * FROM (SELECT * FROM {$tablePhone} WHERE up_abnormal = 1) AS tp LEFT JOIN 
        (SELECT * FROM {$tablePhoneLog} WHERE up_abnormal = 1) AS tpl USING(up_phone)";

echo $sql;
exit;

