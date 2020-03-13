<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2020/3/4
 * Time: 11:44
 */

define("DS", DIRECTORY_SEPARATOR);
define("DIR", __DIR__);

define("CONFIG", DIR . DS . 'config');

// 引入加载器
include 'core/Loader.php';
// 注册自动加载
spl_autoload_register('Loader::autoload');
// 启动
core\Boot::run();

/*$test = new script\四川移动掌上营业厅([
    'JSESSIONID' => 'b1yo2tSaRo7VH1JOJZej8j43VZ8TdLoSM5W20YUbG9rCAokHPm4M',
    'SSOCookie' => 'BEAA91B83BAF258C7CF1717320B43595',
]);

var_dump($test->防疫知识());*/
