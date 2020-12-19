---
typora-copy-images-to: picture
typora-root-url: picture
---

# Laravel组件-wechat

## 公众号对接

### 开放平台

1. 在微信开放平台登录 [公众号账号](https://mp.weixin.qq.com/cgi-bin/home?t=home/index&lang=zh_CN&token=411911080) ，找到最下面的 「基本配置」：

![1608126887509](/1608126887509.png)

2. 修改其中的配置，按照自己的情况设置。配置成功后点击右侧的修改配置：

    ![1608185158837](/1608185158837.png)

3. 填写信息：

![1608185260801](/1608185260801.png)

> - url 这里使用 「内网穿透」，并设置与微信对接的页面。
> - token 可以自己设置。
>
> 完毕后点击提交，提交成功则对接完成。

### 对接代码

可以参考 [接入指南](https://developers.weixin.qq.com/doc/offiaccount/Basic_Information/Access_Overview.html)，其代码：

```php
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
    echo $_GET["echostr"];// 这里是 echostr 随机字符串
}else{
    echo false;
}
```

提交时需要传递对应参数：http://fruhrm.natappfree.cc/index.php?signature=685e6e33328df1e9b94b7c83aa8ab9d631992264&timestamp=123456&nonce=7890 

> 注意：$signature 是加密后的内容，传递时，可以直接传递 $tmpStr 的值。

## 组件编写

### 组件初始化

可以使用两种方式初始化组件包，一个是直接使用 composer init 初始化，手动构建其他目录。另一个是安装扩展包，通过扩展包初始化组件包 - 该方式的优势是可以构建目录，这里主要使用第二种方式。

**构建组件** 

1. 安装创建工具「也可以通过 init 初始化」：
   composer global require "overtrue/package-builder" --prefer-source

> 可以不使用全局安装，在当前页面使用该扩展。

2. 创建 扩展包 结构：
   package-builder build ./组件名称

> 在 build 的 bat 文件目录中运行，生成在当前目录。

3. 安装依赖：

   通过 composer install 安装依赖以及自动加载等。

### 功能编写

.WeChatController 信息回复：

```php
namespace Harry\Wechat\Http\Controller;

use Illuminate\Http\Request;

class WeChatController
{
    public function index()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        // 只有在第一次对接的时候才会存在  因此可以根据这个参数来判断是否之前校验过
        $echostr = $_GET['echostr'];

        // 加密过程
        $token = "xam";
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            // 额外修改的代码  是否有关联
            if (empty($echostr)) {

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
                // 把百分号（%）符号替换成一个作为参数进行传递的变量：

                $info = sprintf('<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[text]]></MsgType>
                <Content><![CDATA[%s]]></Content>
              </xml>', $fromUserName, $toUserName, $time, $content);

                return $info;
            } else {
                return $echostr;
            }
        }else{
            return "false";
        }
    }
}
```

首页 index.php：

```php
require_once '../vendor/autoload.php';

use Harry\Wechat\Http\Controller\WeChatController;

$w = new WeChatController();
echo $w->index();
```

对接时，直接将这里的 index.php 代替上面对接的 index.php；在微信公众号中修改配置并提交，验证通过即可。

### 信息回复

使用手机关注公众号，发送消息。此时可以自动回复消息 “我也......”。公众号信息可以通过以下方式查看：

![1608205090418](/1608205090418.png)

## 对接 Laravel

### 安装

1. **配置本地扩展包**：

   ```
   composer config repositories.wechat path ../wechat
   ```

   以上内容会在 框架的 composer.json 文件中添加以下内容：

   ```json
   "repositories": {
       "wechat": {
           "type": "path",
           "url": "../wechat"
       }
   }
   ```

   其含义 当在对应的镜像没有找到该包时，会从配置的位置安装该扩展包。

2. **安装**：

   ```
   composer require harry/wechat:dev-master
   ```

   安装 wechat 的 master 分支的 稳定版本。

### 路由

1. **添加路由**:

   在 src\Http\routes.php 文件中添加以下内容:

   ```php
   use Illuminate\Support\Facades\Route;
   
   Route::any('/','Harry\Wechat\Http\Controller\WeChatController@index');
   ```

   以上路由创建后无法使用，需要提前加载。

2. **服务提供者 之 加载路由**：

   服务提供者 - harry\weather\src\WeatherServiceProvider.php：

   ```php
   namespace Harry\Wechat\providers;
   
   use Illuminate\Support\ServiceProvider;
   use Illuminate\Support\Facades\Route;
   
   class WechatServiceProvider extends ServiceProvider
   {
       # 后执行 - 加载视图
       public function boot()
       {
           # WechatServiceProvider-加载路由
           $this->registerRoutes();
       }
   
       # 创建路由 - 注册路由
       public function registerRoutes()
       {
           # WechatServiceProvider-路由组信息
           Route::group($this->routeConfiguration(), function () {
               # ServiceProvider - 加载指定路由
               $this->loadRoutesFrom(__DIR__.'/../Http/routes.php');
           });
       }
   
       # 路由组信息  「可以不用」
       private function routeConfiguration()
       {
           return [
               'namespace' => 'Harry\Wechat\Http\Controller',
               'prefix' => 'wechat',
           ];
       }
   }
   ```
   
   注册服务提供者：
   
   ```php
\Harry\Wechat\providers\WechatServiceProvider::class,
   ```

   在 laravel 的 app.php 的文件中添加该服务提供者，通过 php artisan route:list 指令查看可以查看到该路由的内容。
   
   ------ 或者 ------：

   在 composer.json 文件中添加以下内容，也可以实现服务提供者的加载：

   ```json
    "extra": {
           "laravel": {
            "provider": [
                   "Harry\Wechat\providers\WechatServiceProvider"
               ]
           }
       }
   ```
   
   > 注意：测试的时候需要把参数的获取换成 laravel 的对象的方式获取。
   >
   > 另外注意：对接到 laravel 之后，需要重新配置 微信开放平台 的信息，为 框架访问 微信回复功能的路由。

### 配置加载

1. **配置文件编写**：

   配置文件的内容 参考公众号的 [接收普通消息](https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Passive_user_reply_message.html) ：
   
   Config\xwechat.php：
   
   ```php
   # 回复的模板
   return [
       'wechat_tmp' => [
           // 文本模板
           'text'  => '
               <xml>
                 <ToUserName><![CDATA[%s]]></ToUserName>
                 <FromUserName><![CDATA[%s]]></FromUserName>
                 <CreateTime>%s</CreateTime>
                 <MsgType><![CDATA[text]]></MsgType>
                 <Content><![CDATA[%s]]></Content>
               </xml>',
           // 图片模板
           'image'  => '
               <xml>
                   <ToUserName><![CDATA[%s]]></ToUserName>
                   <FromUserName><![CDATA[%s]]></FromUserName>
                   <CreateTime>%s</CreateTime>
                   <MsgType><![CDATA[image]]></MsgType>
                   <Image>
                       <MediaId><![CDATA[%s]]></MediaId>
                   </Image>
               </xml>',
           // 图文模板
           'news'  =>[
               'TplHead' => '
                     <xml>
                       <ToUserName><![CDATA[%s]]></ToUserName>
                       <FromUserName><![CDATA[%s]]></FromUserName>
                       <CreateTime>%s</CreateTime>
                       <MsgType><![CDATA[news]]></MsgType>
                       <ArticleCount>%s</ArticleCount>
                       <Articles>',
               'TplBody' => '
                       <item>
                           <Title><![CDATA[%s]]></Title>
                           <Description><![CDATA[%s]]></Description>
                           <PicUrl><![CDATA[%s]]></PicUrl>
                           <Url><![CDATA[%s]]></Url>
                       </item>',
               'TplFoot' => '
                       </Articles>
                     </xml>'
           ],
       ]
   ];
   ```
   
2. **服务提供者 之 配置文件加载**：

   ```php
   namespace Harry\Wechat\providers;
   
   class WechatServiceProvider extends ServiceProvider
   {
   	......
       # ---------------- 2 加载配置文件 ----------------
   
       public function register()
       {
           # ServiceProvider - 将给定配置与现有配置合并
           $this->mergeConfigFrom(__DIR__ . '/../Config/xwechat.php','wechat');
       }
   }
   ```

   创建路由并访问来查看是否加载对应配置文件：

   ```php
   Route::any('config', function(){
       dump(config()->all());
   });
   ```

   成功创建了配置文件之后，在控制器中的消息回复模板可以通过配置文件来设置：

   ```php
   $info = sprintf(config('wechat.wechat_tmp.text'), $fromUserName, $toUserName, $time, $content);
   ```

   通过公总号测试，可以成功回复消息。

   > 自己这里是在组件中编写，需要加上给定的前缀 wechat 。
   >
   > .
   >
   > 补充 - 另外还可以加载视图：
   >
   > 1. 创建视图:
   >
   >    Resources\views\welcome.blade.php：
   >
   >    ```php+HTML
   >    <!doctype html>
   >    <html lang="en">
   >    <head>
   >        <meta charset="UTF-8">
   >        <meta name="viewport"
   >              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
   >        <meta http-equiv="X-UA-Compatible" content="ie=edge">
   >        <title>wechat</title>
   >    </head>
   >    <body>
   >        欢迎来到手写 wechat 自动回复
   >    </body>
   >    </html>
   >    ```
   >
   > 2. 服务提供者 之 加载视图：
   >
   >    ```php
   >    namespace Harry\Wechat\providers;
   >    
   >    class WechatServiceProvider extends ServiceProvider
   >    {
   >        # ---------------- 补充 - 加载视图 ----------------
   >        public function boot()
   >        {
   >            # WechatServiceProvider-加载视图
   >            $this->loadViewsFrom(
   >                __DIR__.'/../Resources/views', 'wechat'
   >            );
   >        }
   >    }
   >    ```
   >
   > 3. 视图路由：
   >
   >    ```php
   >    Route::any('hello', function(){
   >        return view('wechat::welcome');
   >    });
   >    ```
   >
   >    <span style='color: red'> 注意</span>：服务提供者中编写的视图，需要加入视图的名称并且通过 “::” 来引用。例如 别名::视图名称 -> wechat::welcome。

## 代码优化

### 中间件

1. **中间件编写**：

   Http\Middlewares\WechatMiddleware.php：

   ```php
   namespace Harry\Wechat\Http\Middlewares;
   
   use Illuminate\Http\Request;
   
   class WechatMiddleware
   {
       public function handle(Request $request, \Closure $next)
       {
          	dump('测试中间件');
   		return $next($request);
       }
   }
   ```

2. **加载中间件**：

   providers\WechatServiceProvider.php：

   ```php
   # 模仿 \Illuminate\Foundation\Http\Kernel.php 注册中间件，通过路由加载该中间件
   protected $routeMiddleware = [
       'wechat.check' => WechatMiddleware::class
   ];
   protected $middlewareGroups = [];
   
   
   public function registerRoutes()
   {
       $this->registerRouteMiddleware();
   }
   
   # 注册中间件  注意 该方法需要在 注册中调用
   protected function registerRouteMiddleware()
   {
       foreach ($this->routeMiddleware as $key => $middleware) {
           $this->app['router']->aliasMiddleware($key, $middleware);
       }
   }
   ```

3. **使用中间件**：

   以 config 路由为例:

   ```php
   Route::any('config', function(){
       dump(config('wechat.wechat_tmp.text'));
   })->middleware('wechat.check');
   ```

   以上除了输出配置文件内容外，还会输出中间件中的内容。

### 控制器内容提取

1. **代码提取到中间件**：

   ```php
   namespace Harry\Wechat\Http\Middlewares;
   
   use Illuminate\Http\Request;
   
   class WechatMiddleware
   {
       
       public function handle(Request $request, \Closure $next)
       {
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
   ```

   把所有参数获取 以及 验证 加密的过程提取到中间件中，控制器中只保留实现代码。

2. **控制器代码**：

   ```php
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
   ```

3. **路由**：

   ```php
   Route::any('/','WeChatController@index')->middleware('wechat.check');
   ```

### 配置文件发布

1. **配置文件发布功能编写**：

   在 providers\WechatServiceProvider.php 中加入：

   ```php
   # ---------------- 4 配置文件发布 ----------------
   
   # 配置文件命令
   public function registerPublishing()
   {
       //php artisan  vendor:publish --provider="Harry\Wechat\providers\WechatServiceProvider"
       if ($this->app->runningInConsole()) { // 是不是在控制台运行
           // 可以发布配置文件到指定目录: 获取配置文件            发布地址(配置文件名称)              分组
           $this->publishes([__DIR__.'/../Config/xwechat.php' => config_path('xwechat.php')], 'xwechat');
       }
   }
   ```

   修改 config_path 方法的参数可以修改发布的配置文件的名称，以及路径。

   注意：需要在 register 中注册：

   ```php
   public function register()
   {
       # 加载命令  4......
       $this->registerPublishing();# 配置文件注册
   }
   ```

2. **命令发布**：

   通过 php artisan vendor:publish --provider=服务提供者 的方式发布命令：

   ```
   php artisan  vendor:publish --provider="Harry\Wechat\providers\WechatServiceProvider"
   ```

   发布成功后会在 blogp\config 目录下生成对应的配置文件。

## 自定义命令

### Laravel 自定义命令

1. **创建命令文件**：

   通过以下命令可以帮我们快速创建命令文件：

   ```bash
   php artisan make:command 命令文件名称「HashCommand」
   ```

   该文件会生成在 app\Console\Commands 目录下。

2. **命令文件说明**：

   ```php
   namespace App\Console\Commands;
   
   use Illuminate\Console\Command;
   
   class HashCommand extends Command
   {
       # 命令名称 例如 make:controller
       protected $signature = 'command:name';
   
       # 命令描述
       protected $description = 'Command description';
   
   
       public function __construct()
       {
           parent::__construct();
       }
   
       # 执行控制台命令  实际的命令逻辑代码
       public function handle()
       {
           return 0;
       }
   }
   ```

   通过 php artisan list 可以看到创建的命令已经存在：

   ![1608295983170](/1608295983170.png)

3. **命令文件编写**：

   这里以创建文件为例，可以借鉴框架提供的文件创建命令 - Illuminate\Foundation\Console\ModelMakeCommand.php

   ```php
   namespace App\Console\Commands;
   
   use Illuminate\Console\GeneratorCommand;
   
   # 需要继承 GeneratorCommand 类，并且重写 getStub 方法
   class ObjectCommand extends GeneratorCommand
   {
       # 命令名称
       protected $name = 'make:object';
   
       # 命令描述
       protected $description = 'crate a new object';
   
       protected $type = 'object';
   
   
       # 获取生成器文件目录
       protected function getStub()
       {
           # 模拟框架的方式 Illuminate\Foundation\Console 中的实现
           return __DIR__.'/stubs/object.stub';
       }
   
       # 获取该类的默认名称空间  $rootNamespace 代表的是 app 目录
       protected function getDefaultNamespace($rootNamespace)
       {
           return $rootNamespace . '/Object';
       }
   }
   ```

   > 注意：此处的 命令名称的属性需要改成 name 属性，而不是 $signature 属性，并且需要继承 GeneratorCommand 类。

4. **生成文件**：

   app\Console\Commands\stubs\object.stub

   ```php
   <?php
   
   namespace DummyNamespace;
   
   class DummyClass
   {
       public function index()
       {
           // 全新的对象
       }
   }
   ```

   生成的文件以 .stub 结尾。后续创建的文件则会以该文件为模板创建。

5. **注册**：

   ```php
   namespace App\Console;
   
   class Kernel extends ConsoleKernel
   {
       protected $commands = [
           ObjectCommand::class,
       ];
   }
   ```

   需要在 app\Console\Kernel.php 文件中注册编写的 命令文件。

6. **使用**：

   以上  命令编写完毕，可以使用命令来生成对应的文件了。例如：php artisan make:object Hello，可以在 app\Object 目录下创建一个 Hello.php 文件。

### 组件命令

1. **创建命令文件**：

   Console\Commands\ControllerMakeCommand.php：

   ```php
   namespace Harry\Wechat\Console\Commands;
   
   use Illuminate\Routing\Console\ControllerMakeCommand as Command;
   use Illuminate\Support\Str;
   
   class ControllerMakeCommand extends Command
   {
       # 命令名称
       protected $name = 'make:wechatc';
   
       # 命令描述
       protected $description = '这是组件中的创建Controller的命令';
   
       # 命名空间
       protected $namespace = "Harry\Wechat\Http\Controller";
   
       # 根据根名称空间解析类名称和格式
       protected function qualifyClass($name)
       {
           $name = ltrim($name, '\\/');
           return $this->namespace.'\\'.$name;
       }
   
       # 获取目标类路径
       protected function getPath($name)
       {
           # $this->rootNamespace() => App => Harry\Wechat
           # var_dump($name);#  "Harry\Wechat\Http\Controller\xwechatTest"
           $name = Str::replaceFirst($this->rootNamespace(), '', $name);
   
           # var_dump(app()->basePath());# 项目根目录
           return app()->basePath().'\\vendor\Harry\Wechat\src\\'.str_replace('\\', '/', $name).'.php';  # ......vendor\xx\wechat\src\Http/Controllers/xwechatTest.php
       }
   
       # 获取类的根名称空间
       public function rootNamespace()
       {
           return "Harry\Wechat\\";# 确定命名空间 一面和路径中的 与 命名空间同名部分重复
       }
   }
   ```

2. **服务提供者 之 注册与加载**：

   harry\wechat\src\providers\WechatServiceProvider.php

   ```php
   namespace Harry\Wechat\providers;
   
   class WechatServiceProvider extends ServiceProvider
   {
       # ---------------- 5 自定义命令 ----------------
       public function register()
       {
           # 加载命令类 - service 类的方法 5......
           $this->commands($this->commands);
       }
       
       # 注册命令类文件
       protected $commands = [
           ControllerMakeCommand::class,
       ];
   }
   ```

3. **命令使用**：

   通过命令 - php artisan make:wechat Hello 可以在 harry\wechat\src\Http\Controller 目录中创建 Hello 控制器。

   以上 完毕。

## 组件发布

### github 管理

详见 github 管理。

1. **创建远程仓库**：

   ```
   需要注册 github 账号，在右侧通过 new repository 来新建一个仓库。
   ```

2. **项目添加与提交**：

   ```
   根据创建的仓库的提示信息来操作，实现项目的远程仓库的推送。
   
   步骤：
       1.git init 
       2. git add .   「这里有一个警告 可以会略」
       3. git status  查看是否添加
       4.git commit -m 'laravel 扩展包 wechat 20201219 提交'   提交描述
       5.git remote add origin https://github.com/1198746404/wechat-finished.git  设置远程仓库
       6. git push -u origin master  推送   注意账户和密码 容易错误
   
   需要注意提交的是否为 当前账号 -》1.检查 ssh key「设置」-》2.设置 username & password -》3.推送失败 查看推送信息 「git remote -v 不对 则 清除 - git remote rm origin  重新设置」
   ```

### Composer 管理

1. **提交仓库**：

   ![1608363541665](/1608363541665.png)

2. **检测问题**：

   检测一般主要会出现以下两个问题：

   ```
   1.包是私有的 需要切换包为public；
   2.厂商已经存在，修改composr.json 文件中的 厂商名称
   ```

3. **自动提交**：

   github 中操作：

   ![1608364622216](/1608364622216.png)

   





























服务提供者能够提前加载的东东有哪些？



安装视图扩展：

```bash
composer require sven/artisan-view
```

































