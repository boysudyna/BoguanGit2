<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
define('LIB_PATH_HOST', dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR.'Library'.DIRECTORY_SEPARATOR);
define('TP_PATH', LIB_PATH_HOST.'ThinkPHP'.DIRECTORY_SEPARATOR);
define('BS_PATH', LIB_PATH_HOST.'bootstrap'.DIRECTORY_SEPARATOR);
define('BS_CSS', BS_PATH.'dist'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR);
define('BS_JS', BS_PATH.'dist'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR);

// 定义应用目录
define('APP_PATH', __DIR__ . '/application/');
// 加载框架引导文件
require TP_PATH . 'thinkphp/start.php';
