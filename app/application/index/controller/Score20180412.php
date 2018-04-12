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
            '12' => '24',
            '13' => '24',
            '14' => '24',
            '15' => '24',
            '16' => '25',
        );
        $douConf = array(
            '100' => '2',
            '500' => '3',
            '1024' => '4',
            '5120' => '5',
            '10240' => '7',
            '20480' => '9',
            '99999' => '10',
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

        $userArr = $db->select();
        foreach ($userArr as $key => $v) {
            $Score = 0;
            $Score += $vipConf[$v['up_vip']];
            if(isset($arpuConf[$v['up_ARPU']]))
                $arpuScore = $arpuConf[$v['up_ARPU']];
            else {
                if($v['up_ARPU'] >= 400){
                    $arpuScore = 25;
                }else{
                    foreach ($arpuConf as $key => $value) {
                        if($key > $v['up_ARPU']){
                            $arpuScore = $value;
                            break;
                        }

                    }
                }

            }

            $Score += $arpuScore;
            $currYear = '2018-04-01';
            $sDate = date_create($currYear);
            $eDate = date_create($v['up_open_date']);
            $yearDiff = date_diff($eDate, $sDate);
            $years = abs($yearDiff->format("%R%y"));
            if(isset($yearConf[$years]))
                $yearScore = $yearConf[$years];
            else {
                $yearScore = 25;
            }

            $Score += $yearScore;

            if(isset($douConf[$v['up_DOU']]))
                $douScore = $douConf[$v['up_DOU']];
            else {
                if($v['up_DOU']>=20480){
                    $douScore = 10;
                }else{
                    foreach ($douConf as $key => $value) {
                        if($key > $v['up_DOU']){
                            $douScore = $value;
                            break;
                        }

                    }
                }
            }

            $Score += $douScore;
            if(isset($mouConf[$v['up_MOU']]))
                $mouScore = $mouConf[$v['up_MOU']];
            else {
                if($v['up_MOU']>=1200){
                    $mouScore = 10; 
                }else{
                    foreach ($mouConf as $key => $value) {
                        if($key > $v['up_MOU']){
                            $mouScore = $value;
                            break;
                        }

                    }
                }
            }

            $Score += $mouScore;

            echo "$Score <br />";
        }


        exit;
        return $this->fetch('index');
    }

}
