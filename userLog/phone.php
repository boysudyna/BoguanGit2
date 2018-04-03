<?php
/*
* 统计用户使用手机频率
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
$fileName = $_GET['file'] ? $_GET['file'] : 'zd';
$handle  = fopen ('./data/'.$fileName.'.tsv', "r");
echo  '开始创建数据库 =====<br />';
$tableName = "t_user_phonelog";

$hasSql = "SHOW TABLES LIKE '{$tableName}'";
$hasRet = $db->getRow($hasSql);
if ($hasRet) {
    $sql = "TRUNCATE TABLE {$tableName}";
    $db->query($sql);
} else {
    $createSql = <<<SQL
        CREATE TABLE {$tableName} (
            `up_id` int(11) NOT NULL AUTO_INCREMENT,
            `up_phone` bigint(11) NOT NULL,
            `up_nums` smallint(2) NOT NULL,
            `up_detail` text NOT NULL,
            `up_abnormal` tinyint(2) NOT NULL,
            PRIMARY KEY (`up_id`),
            KEY `up_phone` (`up_phone`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户手机使用频率'
SQL;
    $db->query($createSql);
}

echo  '数据库创建完成 =====<br />';
echo  '开始执行写入 =====<br />';
$stime = microtime(true);
$i = $ri = $rj = 0;
$beginNum = 20000000;
$endNum = 27000000;
$prevP = '';
$insData = $preData = array();
$sql = "INSERT INTO {$tableName} (`up_id`,`up_phone`,`up_nums`,`up_detail`,`up_status`) VALUES ";
while (!feof ($handle)) {
    $data = array();
    $buffer  = fgets($handle, 1024);
    // 跳过第一条数据字段名
    if ($i == 0)  {
        $i ++;
        continue;
    }  

    // $rj ++;
    // if($rj > $endNum)
    //     break;

    // if($rj < $beginNum)
    //     continue;

    $strings = trim($buffer);
    $strings = $buffer;
    $strings = iconv("GB2312", "UTF-8//IGNORE", $strings);
    $arr = explode("\t", $strings);
    array_map(addslashes, $arr);
    $data['up_id'] = '';    
    $data['up_phone'] = trim($arr[4]);    
    $data['up_nums'] = 1;    
    $data['up_detail'] = $arr[1].'_'.$arr[2].'_'.$arr[3]; 
    $data['up_status'] = 0;
    if(! $data['up_phone']) {
        continue;
    }

    $currP = $data['up_phone'];
    if ($currP && $currP == $prevP) {
        $insData['up_phone'] = $data['up_phone'];
        $insData['up_detail'] = $insData['up_detail'].';'.$data['up_detail'];
        $insData['up_nums']++;
    } else {
        $i++;
        if($ri == 1){
            $arrStr .= '(\'' . implode('\',\'', $insData) . '\'),';
        }

        $ri = 1;
        $insData = $data;

        if ($i % 1000 == 0) {
            $sqlStr = substr($sql . $arrStr, 0, -1);
            $rr = $db->query($sqlStr);
            $arrStr = '';
        } 
    }

    $prevP = $currP;
}

if ($arrStr) {
    if($insData['up_nums'] > 1)
        $arrStr .= '(\'' . implode('\',\'', $insData) . '\'),';

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

