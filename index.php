<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2020/3/4
 * Time: 11:44
 */

define('DS', DIRECTORY_SEPARATOR);
define('DIR', __DIR__);

define('CONFIG', DIR . DS . 'config');

//返回执行结果
define('R_OK', 1);
define('R_NO', 0);
define('R_ERROR', -1);

// 引入加载器
include 'core/Loader.php';
// 注册自动加载
spl_autoload_register('Loader::autoload');
// 启动
core\Boot::run();