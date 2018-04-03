<?php
namespace app\index\controller;

use think\Controller;
use think\Db;

set_time_limit(0);
class Phone extends Controller
{
    public function index()
    {	
        $db = Db('t_user_phonelog_test'); // Db::table('t_user_phone') , Db::name('t_user_phone');
        $allTotal = $db->field('count(*) as S')->cache('allLogTotal', 120)->count(); // \think\Cache::get('allTotal');
        echo '总记录数：'.$allTotal . '<br />';

        $numArr = [0,3,5,10,15,20];
        $where = array();
        foreach ($numArr as $key => $value) {
            $last = $numArr[$key+1] ? $numArr[$key+1] : 5000;
            $where['up_nums'] = array(array('gt', $value),array('elt', $last));
            // $ret = $db->field('count(*) as S')->where($where)->where('up_abnormal=0')->count();
            // $abRet = $db->field('count(*) as S')->where($where)->where('up_abnormal=1')->count();

            // echo $ret .':'.$abRet .'<br />';
        }

        $phoneArr = ['apple'=>'苹果', 'sx'=>'三星', 'hw'=>'华为', 'oppo'=>'欧珀', 'xm'=>'小米']; // 一个汉字3个字符
        foreach ($phoneArr as $key => $value) {
            $colSql = "show columns from `t_user_phonelog_test` like 'up_{$key}'";
            if(! $db->query($colSql)) {
                $createSql = "ALTER TABLE  `t_user_phonelog_test` ADD `up_{$key}` INT NOT NULL DEFAULT '0'";
                $db->execute($createSql);
            }
        }

        $upData = array();
        $upData['up_apple'] = "((length(up_detail)-length(replace(up_detail,'苹果','')))/6)";
        $upData['up_sx'] = "((length(up_detail)-length(replace(up_detail,'三星','')))/6)";
        $upData['up_hw'] = "((length(up_detail)-length(replace(up_detail,'华为','')))/6)";
        $upData['up_oppo'] = "((length(up_detail)-length(replace(up_detail,'欧珀','')))/6)";
        $upData['up_xm'] = "((length(up_detail)-length(replace(up_detail,'小米','')))/6)";

        foreach ($upData as $key => $value) {
            $condSql .= $condSql ? ', '.$key .'='.$value : $key .'='.$value;
        }

        $offset = 0;
        $limit = 1000;
        do {
            // update t_user_phonelog_test set up_apple = ((length(up_detail)-length(replace(up_detail,'苹果','')))/6) where up_nums >=5 limit 10
            // $ret = $db->where('1=1')->limit($offset, $limit)->update($upData); // mysql 更新limit 不能从多少行开始
            $sql = "UPDATE t_user_phonelog_test Set {$condSql} 
                    WHERE up_id IN(SELECT up_id FROM (SELECT up_id FROM t_user_phonelog_test ORDER BY up_id ASC LIMIT {$offset}, {$limit}) AS t)";
            // $db->execute($sql);
            $offset += $limit;
        } while ($offset <= $allTotal);

    	echo '==计算结束==';
    	exit;
        return $this->fetch('index');
    }
}
