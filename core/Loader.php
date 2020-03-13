<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2020/3/4
 * Time: 11:58
 */

class Loader
{
    /**
     *路径映射
     */
    public static $vendorMap = array(
        'script' => DIR . DS . 'script',
        'core' => DIR . DS . 'core',
    );

    /**
     * 自动加载器
     */
    public static function autoload(string $class): void
    {
        $file = self::findFile($class);
        if (file_exists($file)) {
            self::includeFile($file);
        }
    }

    /**
     * 解析文件路径
     */
    private static function findFile(string $class): string
    {
        $vendor = substr($class, 0, strpos($class, '\\')); // 顶级命名空间
        $vendorDir = self::$vendorMap[$vendor]; // 文件基目录
        $filePath = substr($class, strlen($vendor)) . '.php'; // 文件相对路径
        return strtr($vendorDir . $filePath, '\\', DS); // 文件标准路径
    }

    /**
     * 引入文件
     */
    private static function includeFile(string $file): void
    {
        if (is_file($file)) {
            include $file;
        }
    }
}