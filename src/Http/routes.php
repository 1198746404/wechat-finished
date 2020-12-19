<?php

use Illuminate\Support\Facades\Route;

//Route::any('/','Harry\Wechat\Http\Controller\WeChatController@index');

//Route::any('/','WeChatController@index');
Route::any('config', function(){
    dump(config('wechat.wechat_tmp.text'));
})->middleware('wechat.check');

Route::any('hello', function(){
    return view('wechat::welcome');
});

# 测试中间件  通过路由
//Route::any('/','WeChatController@index')->middleware('wechat.check');

