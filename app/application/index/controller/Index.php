<?php
namespace app\index\controller;

use think\Controller;
use think\Db;
set_time_limit(0);
header("Content-Type: text/html; charset=utf-8");
class Index extends Controller
{
    public function index()
    {	
        echo str_pad(" ", 4096);  
        $db = Db('t_user_phone'); // Db::table('t_user_phone') , Db::name('t_user_phone');
        echo "\t\t提示：参数year表示网龄，其他还有mou，dou，vip，arpu各自代表查询条件，abnormal=1表示取异常数<br />";
        echo "\t\t配置规则：year=3-5, 表示网龄3-5年，year=3，表示网龄小于3年<br />";
        echo "\t\t配置规则：mou=300-500, 表示300<=mou<500，mou=300，表示mou小于300（dou，arpu规则一致）<br />";
        echo "\t\t配置规则：vip=3-5, 表示vip取3，4，5，year=1，表示vip=1<br />";
        echo "\t\t地址示例：http://xxxxx?year=3-5&mou=300&vip=3&abnormal=1(取网龄3-5年，mou小于300，vip=3的异常数据)<br />";
        echo "\t\t数据正在查询进行中...<br /><br /><br />";
        ob_flush();
        flush(); 

        $year = input('get.year');
        $mou = input('get.mou');
        $dou = input('get.dou');
        $vip = input('get.vip');
        $arpu = input('get.arpu');
        $ab = input('get.abnormal') ? 1 : 0;

        $where['up_abnormal'] = 0;
        $currYear = '2018-04-01';
        if($year){
            list($sYear, $eYear) = explode('-', $year);
            if($sYear)
                $sYear = date('Y-m-d', strtotime("{$currYear} -{$sYear} year"));
            if($eYear)
                $eYear = date('Y-m-d', strtotime("{$currYear} -{$eYear} year"));

            if($eYear){
                $where['up_open_date'] = array(array('egt', $eYear), array('lt', $sYear));
                $str = "网龄介于:{$eYear}-{$sYear}";
            }else{
                $where['up_open_date'] = array(array('lt', $sYear));
                $str = "网龄小于:{$sYear}";
            }
        }

        if($mou){
            list($sMou, $eMou) = explode('-', $mou);
            if($eMou){
                $where['up_MOU'] = array(array('egt', $sMou), array('lt', $eMou));
                $str .= ", MOU介于:{$sMou}-{$eMou}";
            }else{
                $where['up_MOU'] = array('lt', $sMou);
                $str .= ", MOU小于:{$sMou}";
            }
        }

        if($dou){
            list($sDou, $eDou) = explode('-', $dou);
            if($eDou){
                $where['up_DOU'] = array(array('egt', $sDou), array('lt', $eDou));
                $str .= ", DOU介于:{$sDou}-{$eDou}";
            }else{
                $where['up_DOU'] = array('lt', $sDou);
                $str .= ", DOU小于:{$sDou}";
            }
        }

        if($vip){
            list($sVip, $eVip) = explode('-', $vip);
            if($eVip){
                $where['up_vip'] = array(array('egt', $sVip), array('elt', $eVip));
                $str .= ", VIP:{$sVip}-{$eVip}";
            }else{
                $where['up_vip'] = array('eq', $sVip);
                $str .= ", VIP:{$sVip}";
            }
        }

        if($arpu){
            list($sArpu, $eArpu) = explode('-', $arpu);
            if($eArpu){
                $where['up_ARPU'] = array(array('egt', $sArpu), array('lt', $eArpu));
                $str .= ", ARPU介于:{$sArpu}-{$eArpu}";
            }else{
                $where['up_ARPU'] = array('lt', $sArpu);
                $str .= ", ARPU小于:{sArpu}";
            }
        }

        if($ab){
            $where['up_abnormal'] = 1;
            $str .= ',异常';
        }

        $ret = $db->field('count(*) as S')->where($where)->count();
        echo '查询完成：<br />';
        echo $str .'的条件<br />';
        echo '查询数据总量：' . $ret . '<br />';
        exit;
    }
}
