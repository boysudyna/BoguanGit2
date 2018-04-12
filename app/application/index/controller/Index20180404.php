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
        $db = Db('t_user_phone'); // Db::table('t_user_phone') , Db::name('t_user_phone');
        $allTotal = $db->field('count(*) as S')->where('up_abnormal=0')->cache('allTotal', 120)->count(); // \think\Cache::get('allTotal');
        $abnormalTotal = $db->field('count(*) as S')->where('up_abnormal=1')->cache('abnormalTotal', 120)->count();
        echo "提示：需要访问一维数据，在地址后面加上?type=1, 二维数据?type=2, 三维数据?type=3 <br />".PHP_EOL;
        $div = "\t";
        // if(isset($_GET['type'])) {
            // header("Content-type:application/octet-stream");
            // header("Accept-Ranges:bytes");
            // header("Content-Disposition: attachment; filename=result{$_GET['type']}.txt");
        // }

        if(input('get.type') == 1){
            // ======================================一维条件开始=====================================
            $fileText = 'result.txt';

            $str = "会员数总量：{$allTotal} \t 异常总量：{$abnormalTotal}".PHP_EOL;
            echo '<br />'.$str;
            // file_put_contents($fileText, $str);
            // 网龄计算
            $yearArr = ['2008-04-01', '2013-04-01', '2015-04-01', '2018-04-01']; // 10+, 5-10, 3-5, 0-3
            $where = array();
        	foreach ($yearArr as $key => $value) {
        		$prev = !$yearArr[$key-1] ? '0000-00-00' : $yearArr[$key-1];
    			// $last = !$yearArr[$key+1] ? '2018-04-01' : $yearArr[$key+1];
    			$curr = $value;
    			if($prev) {
    				$ret = $db->field('count(*) as S')->where('up_open_date', 'egt', $prev)->where('up_open_date', 'lt', $curr)->where('up_abnormal=0')->count();
    				$abRet = $db->field('count(*) as S')->where('up_open_date', 'egt', $prev)->where('up_open_date', 'lt', $curr)->where('up_abnormal=1')->count();
    				$per = round($ret/$allTotal, 2);
    				$abPer = round($abRet/$abnormalTotal, 2);
    				$str = "从{$prev}到{$curr} 会员总数：\t{$ret}，占比：\t{$per} \t 异常总数：\t{$abRet}，占比：\t{$abPer}".PHP_EOL;
                    echo '<br />'.$str;
    				// file_put_contents($fileText, $str, FILE_APPEND);
    			}
        	}

            // file_put_contents($fileText, PHP_EOL, FILE_APPEND);
            // VIP计算
        	$where = array();
        	for($i = -1; $i <= 7; $i++) {
        		$where['up_vip'] = $i;
        		$ret = $db->field('count(*) as S')->where($where)->where('up_abnormal=0')->count();
        		$abRet = $db->field('count(*) as S')->where($where)->where('up_abnormal=1')->count();
        		$per = round($ret/$allTotal, 2);
    			$abPer = round($abRet/$abnormalTotal, 2);
    			$str = "VIP等级 {$i} 级的会员总数 ：\t{$ret}，占比：\t{$per} \t 异常总数：\t{$abRet}，占比：\t{$abPer}".PHP_EOL;
                echo '<br />'.$str;
    			// file_put_contents($fileText, $str, FILE_APPEND);
        	}

            // file_put_contents($fileText, PHP_EOL, FILE_APPEND);
        	// APRU计算
        	$where = array();
        	$arpuArr = [50, 100, 200, 300, 500];
        	foreach($arpuArr as $key => $value) {
    			$prev = !$arpuArr[$key-1] ? '0.00' : $arpuArr[$key-1];
    			$last = !$arpuArr[$key+1] ? '' : $arpuArr[$key+1];
    			$curr = $value;
    			if($prev) {
    				$ret = $db->field('count(*) as S')->where('up_ARPU', 'egt', $prev)->where('up_ARPU', 'lt', $curr)->where('up_abnormal=0')->count();
    				$abRet = $db->field('count(*) as S')->where('up_ARPU', 'egt', $prev)->where('up_ARPU', 'lt', $curr)->where('up_abnormal=1')->count();
    				$per = round($ret/$allTotal, 2);
    				$abPer = round($abRet/$abnormalTotal, 2);
    				$str = "ARPU从{$prev}到{$curr} 会员总数：\t{$ret}，占比：\t{$per} \t 异常总数：\t{$abRet}，占比：\t{$abPer}".PHP_EOL;
                    echo '<br />'.$str;
    				// file_put_contents($fileText, $str, FILE_APPEND);
    			}

    			if(! $last) {
    				$where['up_ARPU'] = array('egt', $curr);
    				$ret = $db->field('count(*) as S')->where($where)->where('up_abnormal=0')->count();
    				$abRet = $db->field('count(*) as S')->where($where)->where('up_abnormal=1')->count();
    				$per = round($ret/$allTotal, 2);
    				$abPer = round($abRet/$abnormalTotal, 2);
    				$str = "ARPU大于{$curr} 会员总数：\t{$ret}，占比：\t{$per} \t 异常总数：\t{$abRet}，占比：\t{$abPer}".PHP_EOL;
                    echo '<br />'.$str;
    				// file_put_contents($fileText, $str, FILE_APPEND);
    			}
    		}

            // file_put_contents($fileText, PHP_EOL, FILE_APPEND);
    		// MOU计算
        	$where = array();
        	$mouArr = [100, 300, 500, 800, 1200];
        	foreach($mouArr as $key => $value) {
    			$prev = !$mouArr[$key-1] ? '0.00' : $mouArr[$key-1];
    			$last = !$mouArr[$key+1] ? '' : $mouArr[$key+1];
    			$curr = $value;
    			if($prev) {
    				$ret = $db->field('count(*) as S')->where('up_MOU', 'egt', $prev)->where('up_MOU', 'lt', $curr)->where('up_abnormal=0')->count();
    				$abRet = $db->field('count(*) as S')->where('up_MOU', 'egt', $prev)->where('up_MOU', 'lt', $curr)->where('up_abnormal=1')->count();
    				$per = round($ret/$allTotal, 2);
    				$abPer = round($abRet/$abnormalTotal, 2);
    				$str = "MOU{$prev}到{$curr} 会员总数：\t{$ret}，占比：\t{$per} \t 异常总数：\t{$abRet}，占比：\t{$abPer}".PHP_EOL;
                    echo '<br />'.$str;
    				// file_put_contents($fileText, $str, FILE_APPEND);
    			}

    			if(! $last) {
    				$where['up_MOU'] = array('egt', $curr);
    				$ret = $db->field('count(*) as S')->where($where)->where('up_abnormal=0')->count();
    				$abRet = $db->field('count(*) as S')->where($where)->where('up_abnormal=1')->count();
    				$per = round($ret/$allTotal, 2);
    				$abPer = round($abRet/$abnormalTotal, 2);
    				$str = "MOU大于{$curr} 会员总数：\t{$ret}，占比：\t{$per} \t 异常总数：\t{$abRet}，占比：\t{$abPer}".PHP_EOL;
                    echo '<br />'.$str;
    				// file_put_contents($fileText, $str, FILE_APPEND);
    			}
    		}

            // file_put_contents($fileText, PHP_EOL, FILE_APPEND);
    		// DOU计算
        	$where = array();
        	$douArr = [100, 500, 1024, 5120, 10240, 20480];
        	foreach($douArr as $key => $value) {
    			$prev = !$douArr[$key-1] ? '0.00' : $douArr[$key-1];
    			$last = !$douArr[$key+1] ? '' : $douArr[$key+1];
    			$curr = $value;
    			if($prev) {
    				$ret = $db->field('count(*) as S')->where('up_DOU', 'egt', $prev)->where('up_DOU', 'lt', $curr)->where('up_abnormal=0')->count();
    				$abRet = $db->field('count(*) as S')->where('up_DOU', 'egt', $prev)->where('up_DOU', 'lt', $curr)->where('up_abnormal=1')->count();
    				$per = round($ret/$allTotal, 2);
    				$abPer = round($abRet/$abnormalTotal, 2);
    				$str = "DOU{$prev}到{$curr} 会员总数：\t{$ret}，占比：\t{$per} \t 异常总数：\t{$abRet}，占比：\t{$abPer}".PHP_EOL;
                    echo '<br />'.$str;
    				// file_put_contents($fileText, $str, FILE_APPEND);
    			}

    			if(! $last) {
    				$where['up_DOU'] = array('egt', $curr);
    				$ret = $db->field('count(*) as S')->where($where)->where('up_abnormal=0')->count();
    				$abRet = $db->field('count(*) as S')->where($where)->where('up_abnormal=1')->count();
    				$per = round($ret/$allTotal, 2);
    				$abPer = round($abRet/$abnormalTotal, 2);
    				$str = "DOU大于{$curr} 会员总数：\t{$ret}，占比：\t{$per} \t 异常总数：\t{$abRet}，占比：\t{$abPer}".PHP_EOL;
                    echo '<br />'.$str;
    				// file_put_contents($fileText, $str, FILE_APPEND);
    			}
    		}

    		// ======================================一维条件结束=====================================
		}
    
        
        if(input('get.type') == 2){
    		// ======================================二维条件开始=====================================
            $fileText = 'result2.txt';
            $str = "会员数总量：{$allTotal} \t 异常总量：{$abnormalTotal}".PHP_EOL;
            echo '<br />'.$str;
            // file_put_contents($fileText, $str);
    		// 初始条件 - 网龄
    		$initYearArr = ['2008-04-01', '2013-04-01'];
            foreach($initYearArr as $key => $value) {
        		$where = array();
    			$yearPrev = $initYearArr[$key-1] ? $initYearArr[$key-1] : '0000-00-00';
    			$yearCurr = $value;
    			if($yearPrev)
    				$where['up_open_date'] = array(array('egt', $yearPrev), array('lt', $yearCurr));
                else
                    $where['up_open_date'] = array('lt', $yearCurr);

                $titleStr = "网龄介于:{$yearPrev}-{$yearCurr}";
    			// 额外条件 - VIP
                $extWhere = array();
    			for($i = -1; $i <= 7; $i++) {
    	    		$extWhere['up_vip'] = $i;
    	    		$vipWhere = array_merge($where, $extWhere);
    				$ret = $db->field('count(*) as S')->where($vipWhere)->where('up_abnormal=0')->count();
    				$abRet = $db->field('count(*) as S')->where($vipWhere)->where('up_abnormal=1')->count();
    				$per = round($ret/$allTotal, 2);
    				$abPer = round($abRet/$abnormalTotal, 2);
    				$currPer = round($abPer/$per, 2);
    				$str = "{$titleStr}, VIP:{$i}\t异常总数：\t{$abRet}\t占比：\t{$abPer}\t会员总数：\t{$ret}\t占比：\t{$per}\t比例：\t{$currPer}".PHP_EOL;
                    echo '<br />'.$str;
    				// file_put_contents($fileText, $str, FILE_APPEND);
    	    	}
    	    	
                // file_put_contents($fileText, PHP_EOL, FILE_APPEND);

    	    	// 额外条件 - ARPU
                $extWhere = array();
    	    	$arpuArr = [100, 300, 500];
    			foreach($arpuArr as $key => $value) {
    				$curr = $value;
    				$last = $arpuArr[$key+1] ? $arpuArr[$key+1] : '';
    				if($last) {
    					$extWhere['up_ARPU'] = array(array('egt', $curr), array('lt', $last));
    					$arpuWhere = array_merge($where, $extWhere);
    					$ret = $db->field('count(*) as S')->where($arpuWhere)->where('up_abnormal=0')->count();
    					$abRet = $db->field('count(*) as S')->where($arpuWhere)->where('up_abnormal=1')->count();
    					$per = round($ret/$allTotal, 2);
    					$abPer = round($abRet/$abnormalTotal, 2);
    					$currPer = round($abPer/$per, 2);
    					$str = "{$titleStr}, ARPU:{$curr}-{$last}\t异常总数：\t{$abRet}\t占比：\t{$abPer}\t会员总数：\t{$ret}\t占比：\t{$per}\t比例：\t{$currPer}".PHP_EOL;
                        echo '<br />'.$str;
    					// file_put_contents($fileText, $str, FILE_APPEND);
    				} 

                    if(!$last) {
                        $extWhere['up_ARPU'] = array(array('egt', $curr));
                        $arpuWhere = array_merge($where, $extWhere);
                        $ret = $db->field('count(*) as S')->where($arpuWhere)->where('up_abnormal=0')->count();
                        $abRet = $db->field('count(*) as S')->where($arpuWhere)->where('up_abnormal=1')->count();
                        $per = round($ret/$allTotal, 2);
                        $abPer = round($abRet/$abnormalTotal, 2);
                        $currPer = round($abPer/$per, 2);
                        $str = "{$titleStr}, ARPU:{$curr}-{$last}\t异常总数：\t{$abRet}\t占比：\t{$abPer}\t会员总数：\t{$ret}\t占比：\t{$per}\t比例：\t{$currPer}".PHP_EOL;
                        echo '<br />'.$str;
                        // file_put_contents($fileText, $str, FILE_APPEND);
                    }
    			}

                // file_put_contents($fileText, PHP_EOL, FILE_APPEND);

    			// 额外条件 - MOU
                $extWhere = array();
    	    	$mouArr = [300, 500, 800, 1200];
    			foreach($mouArr as $key => $value) {
    				$curr = $value;
    				$last = $mouArr[$key+1] ? $mouArr[$key+1] : '';
    				if($last) {
    					$extWhere['up_MOU'] = array(array('egt', $curr), array('lt', $last));
    					$mouWhere = array_merge($where, $extWhere);
    					$ret = $db->field('count(*) as S')->where($mouWhere)->where('up_abnormal=0')->count();
    					$abRet = $db->field('count(*) as S')->where($mouWhere)->where('up_abnormal=1')->count();
    					$per = round($ret/$allTotal, 2);
    					$abPer = round($abRet/$abnormalTotal, 2);
    					$currPer = round($abPer/$per, 2);
    					$str = "{$titleStr}, MOU:{$curr}-{$last}\t异常总数：\t{$abRet}\t占比：\t{$abPer}\t会员总数：\t{$ret}\t占比：\t{$per}\t比例：\t{$currPer}".PHP_EOL;
                        echo '<br />'.$str;
    					// file_put_contents($fileText, $str, FILE_APPEND);
    				}

                    if(!$last) {
                        $extWhere['up_MOU'] = array(array('egt', $curr));
                        $mouWhere = array_merge($where, $extWhere);
                        $ret = $db->field('count(*) as S')->where($mouWhere)->where('up_abnormal=0')->count();
                        $abRet = $db->field('count(*) as S')->where($mouWhere)->where('up_abnormal=1')->count();
                        $per = round($ret/$allTotal, 2);
                        $abPer = round($abRet/$abnormalTotal, 2);
                        $currPer = round($abPer/$per, 2);
                        $str = "{$titleStr}, MOU:{$curr}-{$last}\t异常总数：\t{$abRet}\t占比：\t{$abPer}\t会员总数：\t{$ret}\t占比：\t{$per}\t比例：\t{$currPer}".PHP_EOL;
                        echo '<br />'.$str;
                        // file_put_contents($fileText, $str, FILE_APPEND);
                    }
    			}

                // file_put_contents($fileText, PHP_EOL, FILE_APPEND);

    			// 额外条件 - DOU
                $extWhere = array();
    	    	$douArr = [100, 500, 1024, 5120, 10240, 20480];
    			foreach($douArr as $key => $value) {
    				$curr = $value;
    				$last = $douArr[$key+1] ? $douArr[$key+1] : '';
    				if($last) {
    					$extWhere['up_DOU'] = array(array('egt', $curr), array('lt', $last));
    					$douWhere = array_merge($where, $extWhere);
    					$ret = $db->field('count(*) as S')->where($douWhere)->where('up_abnormal=0')->count();
    					$abRet = $db->field('count(*) as S')->where($douWhere)->where('up_abnormal=1')->count();
    					$per = round($ret/$allTotal, 2);
    					$abPer = round($abRet/$abnormalTotal, 2);
    					$currPer = round($abPer/$per, 2);
    					$str = "{$titleStr}, DOU:{$curr}-{$last}\t异常总数：\t{$abRet}\t占比：\t{$abPer}\t会员总数：\t{$ret}\t占比：\t{$per}\t比例：\t{$currPer}".PHP_EOL;
                        echo '<br />'.$str;
    					// file_put_contents($fileText, $str, FILE_APPEND);
    				}

                    if(!$last){
                        $extWhere['up_DOU'] = array(array('egt', $curr));
                        $douWhere = array_merge($where, $extWhere);
                        $ret = $db->field('count(*) as S')->where($douWhere)->where('up_abnormal=0')->count();
                        $abRet = $db->field('count(*) as S')->where($douWhere)->where('up_abnormal=1')->count();
                        $per = round($ret/$allTotal, 2);
                        $abPer = round($abRet/$abnormalTotal, 2);
                        $currPer = round($abPer/$per, 2);
                        $str = "{$titleStr}, DOU:{$curr}-{$last}\t异常总数：\t{$abRet}\t占比：\t{$abPer}\t会员总数：\t{$ret}\t占比：\t{$per}\t比例：\t{$currPer}".PHP_EOL;
                        echo '<br />'.$str;
                        // file_put_contents($fileText, $str, FILE_APPEND);
                    }
    			}

                // file_put_contents($fileText, PHP_EOL, FILE_APPEND);
    		}
    		// ======================================二维条件结束=====================================
        }
        
        if(input('get.type') == 3){
            // ======================================三维条件开始=====================================
            $fileText = 'result3.txt';
            $str = "会员数总量：{$allTotal} \t 异常总量：{$abnormalTotal}".PHP_EOL;
            echo '<br />'.$str;
            // file_put_contents($fileText, $str);
            // 初始条件 - 网龄
            $initYearArr = ['2008-04-01', '2013-04-01'];
            foreach($initYearArr as $key => $value) {
                $where = array();
                $yearPrev = $initYearArr[$key-1] ? $initYearArr[$key-1] : '0000-00-00';
                $yearCurr = $value;
                if($yearPrev)
                    $where['up_open_date'] = array(array('egt', $yearPrev), array('lt', $yearCurr));
                else
                    $where['up_open_date'] = array('lt', $yearCurr);

                // 二维条件 - VIP
                for($i = 5; $i <= 7; $i++) {
                    $where['up_vip'] = $i;
                    $titleStr = "网龄介于:{$yearPrev}-{$yearCurr}, VIP:{$i}";
                    // 额外条件 - ARPU
                    $extWhere = array();
                    $arpuArr = [300, 500];
                    foreach($arpuArr as $key => $value) {
                        $curr = $value;
                        $last = $arpuArr[$key+1] ? $arpuArr[$key+1] : '';
                        if($last) {
                            $extWhere['up_ARPU'] = array(array('egt', $curr), array('lt', $last));
                            $arpuWhere = array_merge($where, $extWhere);
                            $ret = $db->field('count(*) as S')->where($arpuWhere)->where('up_abnormal=0')->count();
                            $abRet = $db->field('count(*) as S')->where($arpuWhere)->where('up_abnormal=1')->count();
                            $per = round($ret/$allTotal, 2);
                            $abPer = round($abRet/$abnormalTotal, 2);
                            $currPer = round($abPer/$per, 2);
                            $str = "{$titleStr}, ARPU:{$curr}-{$last}\t异常总数：\t{$abRet}\t占比：\t{$abPer}\t会员总数：\t{$ret}\t占比：\t{$per}\t比例：\t{$currPer}".PHP_EOL;
                            // file_put_contents($fileText, $str, FILE_APPEND);
                            echo '<br />'.$str;
                        }

                        if(!$last) {
                            $extWhere['up_ARPU'] = array(array('egt', $curr));
                            $arpuWhere = array_merge($where, $extWhere);
                            $ret = $db->field('count(*) as S')->where($arpuWhere)->where('up_abnormal=0')->count();
                            $abRet = $db->field('count(*) as S')->where($arpuWhere)->where('up_abnormal=1')->count();
                            $per = round($ret/$allTotal, 2);
                            $abPer = round($abRet/$abnormalTotal, 2);
                            $currPer = round($abPer/$per, 2);
                            $str = "{$titleStr}, ARPU:{$curr}-{$last}\t异常总数：\t{$abRet}\t占比：\t{$abPer}\t会员总数：\t{$ret}\t占比：\t{$per}\t比例：\t{$currPer}".PHP_EOL;
                            // file_put_contents($fileText, $str, FILE_APPEND);
                            echo '<br />'.$str;
                        }
                    }

                    // 额外条件 - MOU
                    $extWhere = array();
                    $mouArr = [500, 800, 1200];
                    foreach($mouArr as $key => $value) {
                        $curr = $value;
                        $last = $mouArr[$key+1] ? $mouArr[$key+1] : '';
                        if($last) {
                            $extWhere['up_MOU'] = array(array('egt', $curr), array('lt', $last));
                            $mouWhere = array_merge($where, $extWhere);
                            $ret = $db->field('count(*) as S')->where($mouWhere)->where('up_abnormal=0')->count();
                            $abRet = $db->field('count(*) as S')->where($mouWhere)->where('up_abnormal=1')->count();
                            $per = round($ret/$allTotal, 2);
                            $abPer = round($abRet/$abnormalTotal, 2);
                            $currPer = round($abPer/$per, 2);
                            $str = "{$titleStr}, MOU:{$curr}-{$last}\t异常总数：\t{$abRet}\t占比：\t{$abPer}\t会员总数：\t{$ret}\t占比：\t{$per}\t比例：\t{$currPer}".PHP_EOL;
                            // file_put_contents($fileText, $str, FILE_APPEND);
                            echo '<br />'.$str;
                        }

                        if(!$last) {
                            $extWhere['up_MOU'] = array(array('egt', $curr));
                            $mouWhere = array_merge($where, $extWhere);
                            $ret = $db->field('count(*) as S')->where($mouWhere)->where('up_abnormal=0')->count();
                            $abRet = $db->field('count(*) as S')->where($mouWhere)->where('up_abnormal=1')->count();
                            $per = round($ret/$allTotal, 2);
                            $abPer = round($abRet/$abnormalTotal, 2);
                            $currPer = round($abPer/$per, 2);
                            $str = "{$titleStr}, MOU:{$curr}-{$last}\t异常总数：\t{$abRet}\t占比：\t{$abPer}\t会员总数：\t{$ret}\t占比：\t{$per}\t比例：\t{$currPer}".PHP_EOL;
                            // file_put_contents($fileText, $str, FILE_APPEND);
                            echo '<br />'.$str;
                        }
                    }

                    // 额外条件 - DOU
                    $extWhere = array();
                    $douArr = [500, 1024, 5120, 10240, 20480];
                    foreach($douArr as $key => $value) {
                        $curr = $value;
                        $last = $douArr[$key+1] ? $douArr[$key+1] : '';
                        if($last) {
                            $extWhere['up_DOU'] = array(array('egt', $curr), array('lt', $last));
                            $douWhere = array_merge($where, $extWhere);
                            $ret = $db->field('count(*) as S')->where($douWhere)->where('up_abnormal=0')->count();
                            $abRet = $db->field('count(*) as S')->where($douWhere)->where('up_abnormal=1')->count();
                            $per = round($ret/$allTotal, 2);
                            $abPer = round($abRet/$abnormalTotal, 2);
                            $currPer = round($abPer/$per, 2);
                            $str = "{$titleStr}, DOU:{$curr}-{$last}\t异常总数：\t{$abRet}\t占比：\t{$abPer}\t会员总数：\t{$ret}\t占比：\t{$per}\t比例：\t{$currPer}".PHP_EOL;
                            // file_put_contents($fileText, $str, FILE_APPEND);
                            echo '<br />'.$str;
                        }

                        if(!$last) {
                            $extWhere['up_DOU'] = array(array('egt', $curr));
                            $douWhere = array_merge($where, $extWhere);
                            $ret = $db->field('count(*) as S')->where($douWhere)->where('up_abnormal=0')->count();
                            $abRet = $db->field('count(*) as S')->where($douWhere)->where('up_abnormal=1')->count();
                            $per = round($ret/$allTotal, 2);
                            $abPer = round($abRet/$abnormalTotal, 2);
                            $currPer = round($abPer/$per, 2);
                            $str = "{$titleStr}, DOU:{$curr}-{$last}\t异常总数：\t{$abRet}\t占比：\t{$abPer}\t会员总数：\t{$ret}\t占比：\t{$per}\t比例：\t{$currPer}".PHP_EOL;
                            // file_put_contents($fileText, $str, FILE_APPEND);
                            echo '<br />'.$str;
                        }
                    }

                    // file_put_contents($fileText, PHP_EOL, FILE_APPEND);
                }
            }
            // ======================================三维条件结束=====================================
        }
        
        // 文件下载
        if(file_exists($fileText)){
            // readfile($fileText);
        }

    	echo '<br />'.'==计算结束==';
    	exit;
        return $this->fetch('index');
    }
}
