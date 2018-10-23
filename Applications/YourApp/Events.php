<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use \GatewayWorker\Lib\Gateway;
use Workerman\Connection\AsyncTcpConnection;
//加载自定义方法
require_once __DIR__ . '/function.php';
//加载数据
require_once __DIR__ . '/data.php';

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    public function __construct()
    {

    }

    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * 
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id)
    {
        $hello = hello();

        // 向当前client_id发送数据 
        Gateway::sendToClient($client_id, "$hello\r\n");

        // 向所有人发送
//        Gateway::sendToAll("$client_id login\r\n");
    }
    
   /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param mixed $message 具体消息
    */
   public static function onMessage($client_id, $message)
   {

       $data = json_decode($message, true);
       $cmd = $data['cmd'];
       $sendData = new data();
       switch ($cmd)
       {
           case 'ping';
               $time = $data['args'][0];
               $push = ping($cmd, $time);
               break;
           case 'hello';
               $push = $sendData->firstData();
               break;
           case 'req';
           if (time() - $data['args'][2] <= 300){
               $push = $sendData->firstData();
           }else{
               $time = $data['args'][2];
               $push = $sendData->history($time);
           }
               break;
           case 'send';
               $send = $sendData->push();
               break;
           case 'push';
               $send = json_encode($data['data']);
               break;
       }

       if (isset($push))
       {
           Gateway::sendToClient($client_id, $push);
       }
       if (isset($send))
       {
           GateWay::sendToAll($send);
       }
   }
   
   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id)
   {
       // 向所有人发送 
//       GateWay::sendToAll("$client_id logout\r\n");
   }

}
