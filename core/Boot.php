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
    private static $arr = ['R_OK' => [], 'R_NO' => [], 'R_ERROR' => []];

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
                    $temp_arr = ['R_OK' => [], 'R_NO' => [], 'R_ERROR' => []];

                    //执行方法
                    foreach ($methods as $method) {
                        if (in_array($method, $appIgnore, true))
                            continue;
                        $arr = $app->$method();
                        //判断是否多个返回值
                        if (isset($arr['result']))
                            switch ($arr['result']) {
                                case R_OK:
                                    $temp_arr['R_OK'][$method] = $arr['msg'];
                                    break;
                                case R_NO:
                                    $temp_arr['R_NO'][$method] = $arr['msg'];
                                    break;
                                case R_ERROR:
                                    $temp_arr['R_ERROR'][$method] = $arr['msg'];
                                    break;
                            }
                        else
                            foreach ($arr as $key => $a)
                                switch ($a['result']) {
                                    case R_OK:
                                        $temp_arr['R_OK']["{$method}({$key})"] = $a['msg'];
                                        break;
                                    case R_NO:
                                        $temp_arr['R_NO']["{$method}({$key})"] = $a['msg'];
                                        break;
                                    case R_ERROR:
                                        $temp_arr['R_ERROR']["{$method}({$key})"] = $a['msg'];
                                        break;
                                }
                        //间歇
                        usleep($time);
                    }
                    //统计
                    foreach ($temp_arr as $R => $V)
                        if ($V)
                            self::$arr[$R][$key] = $V;
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
        var_dump(json_encode(self::$arr));
        /*echo '<br />';
        echo json_encode(self::$suc);
        echo '<br />';
        var_dump(self::$total_suc);
        echo '<br />';
        echo json_encode(self::$fai);
        echo '<br />';
        var_dump(self::$total_fai);*/
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