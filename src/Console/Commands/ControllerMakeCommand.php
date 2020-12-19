<?php

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