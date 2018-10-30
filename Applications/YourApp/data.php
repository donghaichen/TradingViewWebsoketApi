<?php
/**
 * Created by PhpStorm.
 * User: donghai
 * Date: 18-10-16
 * Time: 下午5:50
 */

class data
{
    public $period_arr;
    public function __construct()
    {
        require_once 'function.php';
        $this->period_arr = array(
                'M5' => 300,
                'M15' => 900,
                'M30' => 1800,
                'H1' => 3600,
                'H2' => 7200,
                'H4' => 14400,
                'H6' => 21600,
                'D1' => 86400
            );
    }
    
    //历史K线数据
    public function get_history_klines($pair, $period, $endtime=0){
        $limit=100;
        if(empty($pair)){
            return false;
        }
        if(empty($period) || !isset($this->period_arr[$period])){
            return false;
        }
        $now = time();
        if(empty($endtime) || $endtime>$now){
            $endtime = $now;
        }
        $pair = strtoupper(trim($pair));
        
        $period_second = $this->period_arr[$period];
        $starttime = $endtime - (($endtime - strtotime(date('Y-m-d', $endtime)))%$period_second) - $period_second*$limit;

        $rs_arr = array();
        for($i=0;$i<=$limit;$i++){
            $stime = $starttime+$period_second*$i;
            if($stime>$now){
                break;
            }
            
            $root = '/home/wwwroot/default/cache/kline/'.$pair.'/'.date('Y-m-d',$stime).'/';
            $file = $root.$period_second.'_'.$stime.'.txt';
            if(file_exists($file)){
                $rs_file = json_decode(file_get_contents($file),true);
                $rs_arr[] = array('id'=>$stime,'open'=>$rs_file['open'],'high'=>$rs_file['high'],'low'=>$rs_file['low'],'close'=>$rs_file['close'],'count'=>$rs_file['amount']);
            }else{
                continue;
            }
        }
        return json_encode(array('data'=>$rs_arr));
    }

    public function firstData($pair, $period)
    {
        return $this->get_history_klines($pair, $period);
    }

    public function history($pair, $period, $time)
    {
        return $this->get_history_klines($pair, $period, $time);
    }

}
