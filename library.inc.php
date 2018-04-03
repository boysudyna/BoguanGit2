<?php
/**
* 全局文件配置
*/

header("content-type:text/html;charset=utf-8");
define('LIB_PATH', dirname(__DIR__).DIRECTORY_SEPARATOR.'Library'.DIRECTORY_SEPARATOR);

include LIB_PATH.'mysqli.class.php';
include LIB_PATH.'curl.class.php';

if (defined('DEBUG')) {
	if (DEBUG == true) {
		ini_set('display_errors', true);
		error_reporting(E_ALL ^ E_NOTICE);
	}
} else {
	define('DEBUG', false);
}