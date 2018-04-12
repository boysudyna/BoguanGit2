<?php
namespace app\index\controller;

use think\Controller;
use think\Db;
set_time_limit(0);
header("Content-Type: text/html; charset=utf-8");
class Score extends Controller
{
    public function index()
    {	
        $db = Db('t_user_phone_abnormal'); // Db::table('t_user_phone') , Db::name('t_user_phone');
        $abnormalTotal = $db->field('count(*) as S')->where('up_abnormal=1')->cache('abnormalTotal', 120)->count();
        $vipConf = array(
            '-1' => '0',
            '0' => '4',
            '1' => '8',
            '2' => '12',
            '3' => '16',
            '4' => '20',
            '5' => '24',
            '6' => '28',
            '7' => '30',
        );
        $arpuConf = array(
            '0' => '1',
            '50' => '3',
            '100' => '4',
            '150' => '7',
            '200' => '10',
            '250' => '13',
            '300' => '16',
            '350' => '19',
            '400' => '22',
            '9999' => '25',
        );
        $yearConf = array(
            '0' => '2',
            '1' => '4',
            '2' => '6',
            '3' => '8',
            '4' => '10',
            '5' => '12',
            '6' => '14',
            '7' => '16',
            '8' => '18',
            '9' => '20',
            '10' => '23',
            '11' => '24',
            '15' => '25',
        );
        $douConf = array(
            '100' => '2',
            '500' => '3',
            '1024' => '4',
            '5120' => '5',
            '10240' => '7',
            '20480' => '9',
            '9999' => '10',
        );
        $mouConf = array(
            '100' => '2',
            '200' => '3',
            '300' => '4',
            '400' => '5',
            '800' => '7',
            '1200' => '9',
            '9999' => '10',
        );

        $scoreArr = array();
        foreach($vipConf as $k => $v){
            $str = $k.',';
            $initScore = $v;
            foreach ($arpuConf as $k2 => $v2) {
                $str2 = $str . $k2 .',';
                $initScore2 = $initScore + $v2;
                foreach ($yearConf as $k3 => $v3) {
                    $str3 = $str2 . $k3 .',';
                    $initScore3 = $initScore2 + $v3;
                    foreach ($mouConf as $k4 => $v4) {
                        $str4 = $str3 . $k4 .',';
                        $initScore4 = $initScore3 + $v4;
                        foreach ($douConf as $k5 => $v5) {
                            $str5 = $str4 . $k5 .',';
                            $perScore = $initScore4 + $v5;
                            $scoreArr[$perScore][] = $str5;
                        }
                    }
                }
            }

        }

        foreach ($scoreArr as $kScore => $vArr) {
            if($kScore < 80)
                continue;
            
            $where = array();
            $count = 0;
            foreach ($vArr as $key => $value) {
                list($vip, $arpu, $year, $dou, $mou) = explode(',', $value);
                if($where) {
                    $sub['up_vip'] = $vip;
                    $sub['up_ARPU'] = $this->paramCount('arpu', $arpu);
                    $sub['up_open_date'] = $this->paramCount('year', $year);
                    $sub['up_DOU'] = $this->paramCount('dou', $dou);
                    $sub['up_MOU'] = $this->paramCount('mou', $mou);
                    $whereOR['sub'][] = $sub;
                }else{
                    $where['up_vip'] = $vip;
                    $where['up_ARPU'] = $this->paramCount('arpu', $arpu);
                    $where['up_open_date'] = $this->paramCount('year', $year);
                    $where['up_DOU'] = $this->paramCount('dou', $dou);
                    $where['up_MOU'] = $this->paramCount('mou', $mou);
                }
            }

            if($whereOR){
                // 闭包里面不能放数组变量 使用变量的话 function($query) use($par)
                $count = $db->field('count(*) as S')->where($where);
                foreach($whereOR['sub'] as $vv) {
                    $count = $count->whereOr(function($query) use($where){
                                $query->where($where);
                            });
                }

                $count = $count->select();
                echo $db->getlastsql().'<br />'; 
                // echo $db->fetchSql(true)->find(1);
            }else{
                $count = $db->field('count(*) as S')->where($where)->count();
            }

            // $per = round($count/$abnormalTotal, 4);
            echo "分数: \t{$kScore} \t 数量: \t {$count} \t 比例: \t {$per} <br/>";
            if($kScore == 8)
                exit;
        }


        exit;
        return $this->fetch('index');
    }


    public function paramCount($param, $value) {
        $arpuConf = array(
            '0' => '1',
            '50' => '3',
            '100' => '4',
            '150' => '7',
            '200' => '10',
            '250' => '13',
            '300' => '16',
            '350' => '19',
            '400' => '22',
            '9999' => '25',
        );
        $yearConf = array(
            '0' => '2',
            '1' => '4',
            '2' => '6',
            '3' => '8',
            '4' => '10',
            '5' => '12',
            '6' => '14',
            '7' => '16',
            '8' => '18',
            '9' => '20',
            '10' => '23',
            '11' => '24',
            '15' => '25',
        );
        $douConf = array(
            '100' => '2',
            '500' => '3',
            '1024' => '4',
            '5120' => '5',
            '10240' => '7',
            '20480' => '9',
            '9999' => '10',
        );
        $mouConf = array(
            '100' => '2',
            '200' => '3',
            '300' => '4',
            '400' => '5',
            '800' => '7',
            '1200' => '9',
            '9999' => '10',
        );

        switch ($param) {
            case 'arpu':
                if($value == 0) {
                    $sql = array(array('lt', $value));
                } else {
                    $curr = $arpuConf[$value];
                    prev($arpuConf);
                    $prev = key($arpuConf);
                    $sql = array(array('egt',$prev), array('lt', $value));
                    if($value == 9999) {
                        $sql = array(array('egt',$prev));
                    }
                }
                break;
            
            case 'year':
                $year = '2018-04-01';
                $curr = $yearConf[$value];
                next($yearConf);
                $next = key($yearConf);
                $currYear = date('Y-m-d', strtotime("{$year} -{$value} year"));
                $nextYear = date('Y-m-d', strtotime("{$year} -{$next} year"));
                $sql = array(array('gt',$nextYear), array('elt', $currYear));
                if($value == 15) {
                    $sql = array(array('elt',$currYear));
                }

                break;

            case 'dou':
                if($value == 100) {
                    $sql = array(array('lt', $value));
                } else {
                    $curr = $douConf[$value];
                    prev($douConf);
                    $prev = key($douConf);
                    $sql = array(array('egt',$value), array('lt', $prev));
                    if($value == 9999) {
                        $sql = array(array('egt',$prev));
                    }
                }
                break;

            case 'mou':
                if($value == 100) {
                    $sql = array(array('lt', $value));
                } else {
                    $curr = $mouConf[$value];
                    prev($mouConf);
                    $prev = key($mouConf);
                    $sql = array(array('egt',$value), array('lt', $prev));
                    if($value == 9999) {
                        $sql = array(array('egt',$prev));
                    }
                }
                break;
        }

        return $sql;
    }
}
