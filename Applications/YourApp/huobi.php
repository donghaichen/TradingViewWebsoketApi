<?php
/**
 * Created by PhpStorm.
 * User: donghai
 * Date: 18-10-22
 * Time: 上午11:43
 */
use \Workerman\Worker;
use \Workerman\WebServer;
use \GatewayWorker\Gateway;
use \GatewayWorker\BusinessWorker;
use \Workerman\Autoloader;
use Workerman\Connection\AsyncTcpConnection;
// 自动加载类
require_once __DIR__ . '/../../vendor/autoload.php';
$time = $argv[2];
var_dump($time);
/*
*请求数据函数
$sub_str type: string e.g market.btcusdt.kline.1min 具体请查看api
$callback type: function 回调函数，当获得数据时会调用
*/
function request($callback, $req_str="market.btcusdt.kline.1min") {
    $GLOBALS['req_str'] = $req_str;
    $GLOBALS['callback'] = $callback;
    $worker = new Worker();
    $worker->onWorkerStart = function($worker) {
        // ssl需要访问443端口
        $con = new AsyncTcpConnection('ws://api.huobi.pro:443/ws');
        // 设置以ssl加密方式访问，使之成为wss
        $con->transport = 'ssl';

        $con->onConnect = function($con) {
            global $time;
            $data = json_encode([
                'req' => $GLOBALS['req_str'],
                'id' => 'id1',
            ]);
            var_dump($data);
            $con->send($data);
        };
        $con->onMessage = function($con, $data) {
            $data = gzdecode($data);
            $data = json_decode($data, true);
            if(isset($data['ping'])) {
                $con->send(json_encode([
                    "pong" => $data['ping']
                ]));
            }else{
                call_user_func_array($GLOBALS['callback'], array($data));
            }
        };
        $con->connect();
    };
    Worker::runAll();
}

/*
*订阅数据函数
$sub_str type: string e.g market.btcusdt.kline.1min 具体请查看api
$callback type: function 回调函数，当获得数据时会调用
*/
function subscribe($callback, $sub_str="market.btcusdt.kline.1min") {
    $GLOBALS['sub_str'] = $sub_str;
    $GLOBALS['callback'] = $callback;
    $worker = new Worker();
    $worker->onWorkerStart = function($worker) {
        // ssl需要访问443端口
        $con = new AsyncTcpConnection('ws://api.huobi.pro:443/ws');
        // 设置以ssl加密方式访问，使之成为wss
        $con->transport = 'ssl';
        $con->onConnect = function($con) {
            $data = json_encode([
                'sub' => $GLOBALS['sub_str'],
                'id' => 'id1'
            ]);
            $con->send($data);
        };
        $con->onMessage = function($con, $data) {
            $data = gzdecode($data);
            $data = json_decode($data, true);
            if(isset($data['ping'])) {
                $con->send(json_encode([
                    "pong" => $data['ping']
                ]));
            }else{
                call_user_func_array($GLOBALS['callback'], array($data));
            }
        };
        $con->connect();
    };
    Worker::runAll();
}

//subscribe(function($data) {
//    var_dump($data);
//});
//
subscribe(function($res) {
    var_dump(json_encode($res));
   if (isset($res['tick']))
   {
       $tick = $res['tick'];
       var_dump($tick);
       $data['id'] = $tick['id'];
       $data['datetime'] = date('Y-m-d H:i:s', $tick['id']);
       $data['high'] = $tick['high'];
       $data['low'] = $tick['low'];
       $data['open'] = $tick['open'];
       $data['close'] = $tick['close'];
       $data['count'] = $tick['count'];
       $data['quote_vol'] = $tick['vol'];

       $client = stream_socket_client('tcp://0.0.0.0:7273');
       if(!$client)exit("can not connect");
       $data = [
           'cmd' => 'push',
           'group' => 'M5_BTC_USDT',
           'data' => $data,
       ];
       fwrite($client, json_encode($data) ."\n");
   }

});
