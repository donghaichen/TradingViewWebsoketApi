<?php
/**
 * Created by PhpStorm.
 * User: donghai
 * Date: 18-10-16
 * Time: 下午5:50
 */

class data
{
    public function __construct()
    {
        require_once 'function.php';
    }

    public function firstData()
    {
        $url = 'https://api.huobipro.com/market/history/kline?period=1min&size=2000&symbol=btcusdt';
        $arr = getJson($url);
        $arr = $arr['data'];
        $arr = array_reverse($arr);
        foreach ($arr as $k=> $v)
        {
            $data[$k]['id'] = $v['id'];
            $data[$k]['datetime'] = date('Y-m-d H:i:s');
            $data[$k]['high'] = $v['high'];
            $data[$k]['low'] = $v['low'];
            $data[$k]['open'] = $v['open'];
            $data[$k]['close'] = $v['close'];
            $data[$k]['count'] = $v['count'];
            $data[$k]['quote_vol'] = $v['vol'];
        }
        return json_encode(compact('data'));
    }

    public function push()
    {
        $url = 'https://api.fcoin.com/v2/market/candles/M1/btcusdt?limit=1';
        $arr = getJson($url);
        $arr = $arr['data'][0];
        return json_encode($arr);
    }

    public function history($time)
    {
        $url = 'https://api.huobipro.com/market/history/kline?period=1min&size=2000&symbol=btcusdt';
        $arr = getJson($url);
        $arr = $arr['data'];
        $arr = array_reverse($arr);
        $i= 0;
        foreach ($arr as $k=> $v)
        {
            $data[$k]['id'] =  $time - 60 * $i;
            $data[$k]['datetime'] = date('Y-m-d H:i:s', $time - 60 * $i);
            $data[$k]['high'] = $v['high'];
            $data[$k]['low'] = $v['low'];
            $data[$k]['open'] = $v['open'];
            $data[$k]['close'] = $v['close'];
            $data[$k]['count'] = $v['count'];
            $data[$k]['quote_vol'] = $v['vol'];
            $i++;
        }
        return json_encode(compact('data'));
    }

}
