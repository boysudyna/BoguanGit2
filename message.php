<?php
define('DEBUG', TRUE);
require './library.inc.php';

define('WL_API_URL', 'http://43.243.130.33:8860/');

echo '开始执行远程请求...<br />';
// 发送短息
$url = WL_API_URL.'sendSms';
$paramJson = sendSmsPar();
// $data = CurlExchange::send($url, $paramJson, 'post');
// $dataArr = json_decode($data, true);

// 变量短信发送
$url = WL_API_URL.'sendVariantSms';
$paramJson = sendVariantSmsPar();
// $data = CurlExchange::send($url, $paramJson, 'post');
// $dataArr = json_decode($data, true);

// 批量短信发送
$url = WL_API_URL.'sendBatchSms';
$paramJson = sendBatchSmsPar();
// $data = CurlExchange::send($url, $paramJson, 'post');
// $dataArr = json_decode($data, true);

// 获取账户余额
$url = WL_API_URL.'QueryAccount';
$tokenPar = getToken();
$paramJson = getQueryPar($tokenPar);
$data = CurlExchange::send($url, $paramJson, 'post');
$dataArr = json_decode($data, true);

// 获取上行消息
// $url = WL_API_URL.'getMO';
// $tokenPar = getToken();
// $paramJson = getQueryPar($tokenPar);
// $data = CurlExchange::send($url, $paramJson, 'post');
// $dataArr = json_decode($data, true);

// 获取报告
// $url = WL_API_URL.'getReport';
// $tokenPar = getToken();
// $paramJson = getQueryPar($tokenPar);
// $data = CurlExchange::send($url, $paramJson, 'post');
// $dataArr = json_decode($data, true);

echo '远程请求结束...<br />';
// 结果解析
if ($dataArr['code'] == 0 && $dataArr['status'] == 'success') {
	echo '执行成功，说明如下：<br />';
	echo $dataArr['respMsg'] .'<br />';
	print_r($dataArr);
} else {
	echo '执行失败，说明如下：<br />';
	echo $dataArr['respMsg'];
	echo '<pre>';
	print_r($dataArr);
}

exit;

// 普通短信发送
function sendSmsPar() {
	$url = WL_API_URL.'sendSms';
	$pwd = '7ZB6BEWVMH'; // 用户密码
	$param = array(
		'cust_code' => '500246', // 用户账号 Must
		'sp_code' => '',	// 长号码
		'content' => '您的验证码是123',	// 短信内容 Must
		'destMobiles' => '15659998345', // 接受号码 Must
		'uid' => '106903510107212', // 业务标识
		'need_report' => 'yes', // 是否回复
		'sign' => '', // 签名内容根据 “短信内容+客户密码”进行MD5编码后获得 Must
	);
	$sign = md5($param['content'].$pwd);
	$param['sign'] = $sign;

	$paramJson = json_encode($param);
	return $paramJson;
}

// 变量短信发送
function sendVariantSmsPar() {
	$pwd = '7ZB6BEWVMH'; // 用户密码
	$param = array(
		'cust_code' => '500246', // 用户账号 Must
		'sp_code' => '',	// 长号码
		'content' => "【博观信息】验证码：尊敬的\${mobile}客户\${var1}，今天天气良好, 事宜外出去\${var2}",	// 短信内容 Must
		'params' => '', // 变量参数
		// 'uid' => '106903510107212', // 业务标识
		'sign' => '', // 签名内容根据 “短信内容+客户密码”进行MD5编码后获得 Must
	);

	$params[] = array(
		'mobile' => '15659998345',
		'vars' => array('VIP8', '福州'),
	);
	$params[] = array(
		'mobile' => '17187463822',
		'vars' => array('VIP9', '福建联通'),
	);

	$sign = md5($param['content'].$pwd);
	$param['params'] = $params;
	$param['sign'] = $sign;

	// {"cust_code":"570061","sp_code":"1234","content":"${var0}用户您好，今天${var1}的天气，晴，温度${var2}度，事宜外出。","params":{"mobile"："手机号码","var":["福州","30"]},{"mobile"："手机号码","var":["厦门","32"]} ,"sign":" fa246d0262c3925617b0c72bb20eeb1d "}
	
	$paramJson = json_encode($param);
	return $paramJson;
}

// 批量短信发送
function sendBatchSmsPar() {
	$pwd = '7ZB6BEWVMH'; // 用户密码
	$param = array(
		'cust_code' => '500246', // 用户账号 Must
		'msgList' => '',	// 短消息对象列表  
		// msgList: [sp_code,content,destMobiles,uid];
		'sign' => '', // 签名内容根据 “短信内容+客户密码”进行MD5编码后获得 Must
	);

	$msgList[] = array(
		'sp_code' => '',
		'content' => '【博观信息】验证码：批量短息测试第一条',
		'destMobiles' => '15659998345,17187463822', // 多个用,隔开
		'uid' => '106903510107212', // 业务标识
	);

	$param['msgList'] = $msgList;
	$sign = md5($msgList[0]['content'].$pwd);
	$param['sign'] = $sign;

	// {"cust_code":"402183","msgList":[{"content":"测试1","destMobiles":"159600xxxxx","sp_code":"","uid":"14000000000"},{"content":"测试2","destMobiles":"159600xxxxx","sp_code":"","uid":"14000000001"}],"sign":"6f50769e84a4f4253d8003c98e92ea16"}

	$paramJson = json_encode($param);
	return $paramJson;
}

// 获取token
function getToken() {
	$url = WL_API_URL.'GetToken';
	$param = array(
		'cust_code' => '500246',
	);

	$paramJson = json_encode($param);
	$data = CurlExchange::send($url, $paramJson, 'post');
	$dataArr = json_decode($data, true);
	return $dataArr;
}

// 获取账户余额、上行消息、状态报告的查询数据
function getQueryPar($opts) {
	$pwd = '7ZB6BEWVMH'; // 用户密码
	$sign = md5($opts['token'].$pwd);
	$param = array(
		'cust_code' => '500246',
		'token_id' => $opts['token_id'],
		'sign' => $sign,
	);

	$paramJson = json_encode($param);
	return $paramJson;
}

// 通道返回错误列表
/**
提交返回响应值code说明：
0     成功
8     流量控制错，超出最高流量
15    通道不支持
27    长短信拆分条数过多
29    错误号码

部分过滤规则用系统生成状态报告返回，生成的状态报告如下（所有协议统一错误码）：
 * MX:0001 签名匹配规则不成功
 * MX:0002 向上级通道提交短信失败
 * MX:0003 单个手机号码当天下行条数超过上限(长短信算1条)
 * MX:0004 短信内容中包含敏感词 
 * MX:0005 模版过滤失败(签名未报备)  
 * MX:0006 通道敏感词
 * MX:0007客户投诉黑名单
 * MX:0008 根据签名无法匹配到通道
 * MX:0009 目标号码在网关黑名单中
 * MX:0010 目标号码不符合手机号码规范
 * MX:0011 目标号码在禁1年黑名单中
 * MX:0012 目标号码在客户退订黑名单中
 * MX:0013 目标号码在网关黑名单中
 */
