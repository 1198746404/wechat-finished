<?php


namespace Harry\Wechat;


class Wechat
{
    public function index()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        define('TOKEN', 'xam');
        $token = TOKEN;

        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
//            echo 'true';
            echo $_GET["echostr"];// 这里是 echostr 随机字符串
        }else{
            echo false;
        }
    }
}