<?php

namespace Harry\Wechat\Http\Middlewares;

use Illuminate\Http\Request;

class WechatMiddleware
{
    public function handle(Request $request, \Closure $next)
    {
//        dump(123465);
//        return $next($request);

        $signature = $request->signature;
        $timestamp = $request->timestamp;
        $nonce = $request->nonce;
        // 只有在第一次对接的时候才会存在  因此可以根据这个参数来判断是否之前校验过
        $echostr = $request->echostr;

        // 加密过程
        $token = "xam";
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            // 额外修改的代码  是否有关联
            if (empty($echostr)) {
                return $next($request);
            } else {
                return response($echostr);
            }
        }else{
            return response(false);
        }
    }
}