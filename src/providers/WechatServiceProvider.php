<?php


namespace Harry\Wechat\providers;

use Harry\Wechat\Console\Commands\ControllerMakeCommand;
use Harry\Wechat\Http\Middlewares\WechatMiddleware;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class WechatServiceProvider extends ServiceProvider
{
    # ---------------- 1 加载路由 ----------------

    public function boot()
    {
        # WechatServiceProvider-加载路由
        $this->registerRoutes();

        # WechatServiceProvider-加载视图   2......
        $this->loadViewsFrom(
            __DIR__.'/../Resources/views', 'wechat'
        );
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


    # ---------------- 2 加载视图和配置文件 ----------------

    public function register()
    {
        # ServiceProvider - 将给定配置与现有配置合并
        $this->mergeConfigFrom(__DIR__ . '/../Config/xwechat.php','wechat');

        # 加载中间件  3......
        $this->registerRouteMiddleware();

        # 加载命令  4......
        $this->registerPublishing();# 配置文件注册

        # 加载命令类 - service 类的方法 5......
        $this->commands($this->commands);
    }


    # ---------------- 3 注册中间件 ----------------

    # 中间件
    protected $routeMiddleware = [
        'wechat.check' => WechatMiddleware::class
    ];
//    protected $middlewareGroups = [];

    # 注册中间件  注意 该方法需要在 注册中调用
    protected function registerRouteMiddleware()
    {
        foreach ($this->routeMiddleware as $key => $middleware) {
            $this->app['router']->aliasMiddleware($key, $middleware);
        }

//        # 可以省略  中间件组
//        foreach ($this->middlewareGroups as $key => $middleware) {
//            $this->app['router']->middlewareGroup($key, $middleware);
//        }
    }


    # ---------------- 4 配置文件发布 ----------------

    # 配置文件命令
    public function registerPublishing()
    {
        // php artisan vendor:publish --provider="ShineYork\LaravelWechat\WeChatServiceProvider"
        if ($this->app->runningInConsole()) { // 是不是在控制台运行
            // 可以发布配置文件到指定目录: 获取配置文件            发布地址(配置文件名称)              分组
            $this->publishes([__DIR__.'/../Config/xwechat.php' => config_path('xwechat.php')], 'xwechat');
        }
    }


    # ---------------- 5 自定义命令 ----------------
    # 注册命令类文件
    protected $commands = [
        ControllerMakeCommand::class,
    ];
}