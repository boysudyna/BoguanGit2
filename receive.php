<?php
// 文件接受端
// echo '这是接收到的数据信息...<br />';

// echo $GLOBALS['HTTP_RAW_POST_DATA']; // 如果传递的是json数据，需用这输出显示 或者 $post = file_get_contents("php://input");


function my_dir($dir) {
    $files = array();
    if(@$handle = opendir($dir)) { //注意这里要加一个@，不然会有warning错误提示：）
    	echo $dir.'<br />';
        while(($file = readdir($handle)) !== false) {
            if($file != ".." && $file != ".") { //排除根目录；
                if(is_dir($dir."/".$file)) { //如果是子文件夹，就进行递归
                    $files[$file] = my_dir($dir."/".$file);
                } else { //不然就将文件的名字存入数组；
                    $files[] = $file;
                }
 
            }
        }
        
        closedir($handle);
        return $files;
    }
}
echo "<pre>";
print_r(my_dir("../work"));
echo "</pre>";


exit;
$json = array(
	'code' =>'',
	'status' => '',
	'message' => '',
);

$json['code'] = 999;
$json['status'] = 'success';
$json['message'] = '就让你成功吧！';

echo json_encode($json); die();