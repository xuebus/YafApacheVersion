<?php

defined ('APP_LIBS_VIEW') || define ('APP_LIBS_VIEW', ROOT_PATH . 'libs/view/');

class Base_Autoloader {

    /**
     * 框架核心组件
     */
    private static $coreClass = array(
        'Smarty'  =>  array('path'=>array(APP_LIBS_VIEW), 'postfix'=>'.class'),
    );

    /**
     * 框架加载器，用以结合其它模块的autoloader。
     * 具体使用方法可以参考框架的Smarty.class.php
     * @param string $autoloader 其它autoloader函数名
     */
    public static function register($autoloader){
        $loaders = spl_autoload_functions();
        foreach($loaders as $loader){
            spl_autoload_unregister($loader);
        }
        spl_autoload_register($autoloader);
        foreach($loaders as $loader){
            spl_autoload_register($loader);
        }
    }

    /**
     * 文件引用器
     * @param string $prefixPath 文件所在文件夹绝对路径
     * @param string $filename 文件名
     * @param strint $postfix 文件后缀
     */
    public static function includeFile($prefixPath, $filename, $postfix = '.php') {
        $_file = $prefixPath.$filename.$postfix;
        if (is_file($_file)) {
            include($_file);
        } else if(!include($_file)) {
            Base_Log::warning('include error', 500, $_file);
        }
    }

    /**
     * 框架autoloader
     * @param string $classname 要加载的类名
     */
    public static function loader($classname) {
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $classname)) {
            exit();
        }
        if (!isset(self::$coreClass[$classname])) {
            Base_Log::warning('include error', 500, $classname);
            //抛出异常 程序中断
            return ;
        }
        $path = '';
        $filename = $classname;//默认文件名称和类名称相同
        if(!is_array(self::$coreClass[$classname])) {
            $path = self::$coreClass[$classname];
        } else {
            if(isset(self::$coreClass[$classname]['path'])) {
                $path = implode('', self::$coreClass[$classname]['path']);
            }
            if(isset(self::$coreClass[$classname]['classname'])) {
                $filename = self::$coreClass[$classname]['filename'];//用手工配置的文件名称覆盖默认的
            }
            if(isset(self::$coreClass[$classname]['postfix'])) {
                $filename .= self::$coreClass[$classname]['postfix'];
            }
        }
        self::includeFile($path, $filename);
    }
}

