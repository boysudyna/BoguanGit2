<?php
// 文件发送端
define('DEBUG', TRUE);
require './library.inc.php';

$param = array(
	'cust_code' => '402183', // 用户账号 Must
	'sp_code' => '',	// 长号码
	'content' => '测试',	// 短信内容 Must
	'destMobiles' => '15659998345', // 接受号码 Must
	'uid' => '14000000000', // 业务标识
	'need_report' => 'yes', // 是否回复
	'sign' => '', // 签名内容根据 “短信内容+客户密码”进行MD5编码后获得 Must
);

$pwd = '123456'; // 用户密码
$sign = md5($param['content'].$pwd);

echo '开始执行远程请求...<br />';
$param['sign'] = $sign;
$url = 'http://localhost/work/receive.php';
$paramJson = json_encode($param);
$data = CurlExchange::send($url, $param, 'post');

$dataStr = json_decode($data, true);
print_r($dataStr);exit;

