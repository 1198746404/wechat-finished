<?php

require_once '../vendor/autoload.php';

use Harry\Wechat\Wechat;
use Harry\Wechat\Http\Controller\WeChatController;

//$w = new Wechat();
//$w->index();

$w = new WeChatController();
echo $w->index();


//$signature = $_GET["signature"];
//$timestamp = $_GET["timestamp"];
//$nonce = $_GET["nonce"];
//
////echo $signature . '<br>';
////echo $timestamp . '<br>';
////echo $nonce . '<br>';
//
//define('TOKEN', 'xam');
//
//$token = TOKEN;
//$tmpArr = array($token, $timestamp, $nonce);
//sort($tmpArr, SORT_STRING);
//$tmpStr = implode( $tmpArr );
//$tmpStr = sha1( $tmpStr );
//
////echo $tmpStr . '<br>';
////echo $signature . '<br>';
//
//if( $tmpStr == $signature ){
////    echo 'success';
//    echo $_GET["echostr"];// 这里是 echostr 随机字符串
//}else{
////    echo 'fail';
//    echo false;
//}

//$signature = $_GET["signature"];// 微信加密签名
//$timestamp = $_GET["timestamp"];// 时间戳
//$nonce = $_GET["nonce"];// 随机数
//
//$token = 'xam';
//$tmpArr = array($token, $timestamp, $nonce);
//sort($tmpArr, SORT_STRING);
//$tmpStr = implode( $tmpArr );
//$tmpStr = sha1( $tmpStr );
//
////var_dump($tmpStr == $signature);
//
//if( $tmpStr == $signature ){
//    echo $_GET["echostr"];// 这里是 echostr 随机字符串
//}else{
//    echo false;
//}