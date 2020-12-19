<?php

namespace Harry\Wechat\Http\Controller;

use Illuminate\Http\Request;

class WeChatController
{
    # 3. 中间件版
    public function index(Request $request)
    {
        // 回复信息:接收微信发送的参数
        $postObj =file_get_contents('php://input');
        $postArr = simplexml_load_string($postObj,"SimpleXMLElement",LIBXML_NOCDATA);

        // 消息内容
        $content = $postArr->Content;
        //接受者
        $toUserName = $postArr->ToUserName;
        // 发送者
        $fromUserName = $postArr->FromUserName;
        // 获取时间戳
        $time = time();

        $content = "我也 $content";
        $info = sprintf(config('wechat.wechat_tmp.text'), $fromUserName, $toUserName, $time, $content);

        return $info;
    }
}

// # 1. 最初版
//    public function index(Request $request)
//    {
//        $signature = $_GET["signature"];
//        $timestamp = $_GET["timestamp"];
//        $nonce = $_GET["nonce"];
//        // 只有在第一次对接的时候才会存在  因此可以根据这个参数来判断是否之前校验过
//        $echostr = $_GET['echostr'];
//
//        // 加密过程
//        $token = "xam";
//        $tmpArr = array($token, $timestamp, $nonce);
//        sort($tmpArr, SORT_STRING);
//        $tmpStr = implode( $tmpArr );
//        $tmpStr = sha1( $tmpStr );
//
//        if( $tmpStr == $signature ){
//            // 额外修改的代码  是否有关联
//            if (empty($echostr)) {
//
//                // 回复信息:接收微信发送的参数
//                $postObj =file_get_contents('php://input');
//                $postArr = simplexml_load_string($postObj,"SimpleXMLElement",LIBXML_NOCDATA);
//
//                // 消息内容
//                $content = $postArr->Content;
//                //接受者
//                $toUserName = $postArr->ToUserName;
//                // 发送者
//                $fromUserName = $postArr->FromUserName;
//                // 获取时间戳
//                $time = time();
//
//                $content = "我也 $content";
//                // 把百分号（%）符号替换成一个作为参数进行传递的变量：
//
//                $info = sprintf('<xml>
//                <ToUserName><![CDATA[%s]]></ToUserName>
//                <FromUserName><![CDATA[%s]]></FromUserName>
//                <CreateTime>%s</CreateTime>
//                <MsgType><![CDATA[text]]></MsgType>
//                <Content><![CDATA[%s]]></Content>
//              </xml>', $fromUserName, $toUserName, $time, $content);
//
//                return $info;
//            } else {
//                return $echostr;
//            }
//        }else{
//            return "false";
//        }
//    }

//# 2.第二版 - 配置文件使用
//class WeChatController
//{
//    public function index(Request $request)
//    {
//        $signature = $request->signature;
//        $timestamp = $request->timestamp;
//        $nonce = $request->nonce;
//        // 只有在第一次对接的时候才会存在  因此可以根据这个参数来判断是否之前校验过
//        $echostr = $request->echostr;
//
//        // 加密过程
//        $token = "xam";
//        $tmpArr = array($token, $timestamp, $nonce);
//        sort($tmpArr, SORT_STRING);
//        $tmpStr = implode( $tmpArr );
//        $tmpStr = sha1( $tmpStr );
//
//        if( $tmpStr == $signature ){
//            // 额外修改的代码  是否有关联
//            if (empty($echostr)) {
//
//                // 回复信息:接收微信发送的参数
//                $postObj =file_get_contents('php://input');
//                $postArr = simplexml_load_string($postObj,"SimpleXMLElement",LIBXML_NOCDATA);
//
//                // 消息内容
//                $content = $postArr->Content;
//                //接受者
//                $toUserName = $postArr->ToUserName;
//                // 发送者
//                $fromUserName = $postArr->FromUserName;
//                // 获取时间戳
//                $time = time();
//
//                $content = "我也 $content";
//                $info = sprintf(config('wechat.wechat_tmp.text'), $fromUserName, $toUserName, $time, $content);
//
//                return $info;
//            } else {
//                return $echostr;
//            }
//        }else{
//            return "false";
//        }
//    }
//}
