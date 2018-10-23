<?php
/**
 * Created by PhpStorm.
 * User: donghai
 * Date: 18-10-16
 * Time: 下午5:39
 */

//获取毫秒
function msec()
{
    return sprintf("%.0f", microtime(true) * 1000);
}

//GET请求
function getJson($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    $res = json_decode(curl_exec($ch), true);
    return $res;
}

//gap: 推送服务器处理此语句的时间和客户端传输的时间差.
//ts: 推送服务器当前的时间.
function ping($type, $time)
{
    $data = [
        'type' => $type,
        'gap' => msec() - $time,
        'ts'  => msec()
    ];
    return json_encode($data);
}

function hello()
{
    $data = [
        'type' => 'hello exwe',
        'ts' => msec()
    ];
    return json_encode($data);
}
