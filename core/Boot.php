<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2020/3/4
 * Time: 11:58
 */

namespace core;

class Boot
{
    //默认忽略的方法
    private static $ignore = ['__construct'];
    //初始成功失败数组
    private static $suc = [];
    private static $fai = [];
    private static $total_suc = 0;
    private static $total_fai = 0;

    public static function run(): void
    {
        //循环执行的用户
        foreach (self::getUsers() as $user) {
            //循环执行的任务
            foreach (self::getApps($user) as $key => $val) {
                //判断是否跳过这个类(用于测试)
                if (isset($val['nop']) && $val['nop'])
                    continue;
                try {
                    //实例化类
                    $appName = 'script\\' . $key;
                    $app = new $appName($val['params'] ?? []);

                    //获取类方法
                    $methods = get_class_methods($app);

                    //合并忽略方法
                    $appIgnore = $val['ignore'] ? array_merge($val['ignore'], self::$ignore) : self::$ignore;

                    //初始化其他参数
                    $time = ($val['time'] ?? 5) * 1000;
                    $temp_arr = ['suc' => [], 'fai' => []];

                    //执行方法
                    foreach ($methods as $method) {
                        if (in_array($method, $appIgnore, true))
                            continue;
                        $arr = $app->$method();
                        //判断是否多个返回值
                        if (isset($arr['result']))
                            if ($arr['result'])
                                $temp_arr['suc'][$method] = $arr['msg'];
                            else
                                $temp_arr['fai'][$method] = $arr['msg'];
                        else
                            foreach ($arr as $key => $a)
                                if ($a['result'])
                                    $temp_arr['suc']["{$method}({$key})"] = $a['msg'];
                                else
                                    $temp_arr['fai']["{$method}({$key})"] = $a['msg'];
                        //间歇
                        usleep($time);
                    }
                    //统计
                    if ($temp_arr['suc'])
                        self::$suc[$key] = $temp_arr['suc'];
                    if ($temp_arr['fai'])
                        self::$fai[$key] = $temp_arr['fai'];
                    self::$total_suc += count($temp_arr['suc']);
                    self::$total_suc += count($temp_arr['fai']);
                    //释放对象
                    $app = null;
                    $methods = null;
                    $temp_arr = null;
                } catch (\Exception $e) {
                    //echo $e->getMessage();
                }
            }
        }
        //按照规范输出
        self::output();
    }

    private static function output(): void
    {
        echo '<br />';
        echo json_encode(self::$suc);
        echo '<br />';
        var_dump(self::$total_suc);
        echo '<br />';
        echo json_encode(self::$fai);
        echo '<br />';
        var_dump(self::$total_fai);
    }

    private static function getApps(string $user): array
    {
        return require_once CONFIG . DS . 'users' . DS . $user . '.php';
    }

    private static function getUsers(): array
    {
        return require_once CONFIG . DS . 'users.php';
    }
}