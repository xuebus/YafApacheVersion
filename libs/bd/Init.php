<?php

/**
 * Yaf全局初始化类。
 *
 */

class Bd_Init
{
    private static $isInit = false;

    public static function init($app_name = null)
    {
        if(self::$isInit)
        {
            return false;
        }
    
        self::$isInit = true;

        // 初始化基础环境
        self::initBasicEnv();

        // 初始化App环境
        self::initAppEnv($app_name);

        // 初始化Yaf框架
        self::initYaf();

        return Yaf_Application::app();
    }

    private static function initBasicEnv()
    {
        // 页面启动时间(us)，PHP5.4可用$_SERVER['REQUEST_TIME']
        define('REQUEST_TIME_US', intval(microtime(true)*1000000));

        //一些缓存的目录，例如smarty的缓存编译目录
        define('CACHE_PATH', '/data0/cache/');
        
        // ODP预定义路径
        define('ROOT_PATH', '/var/htdocs/levin/Yaf/');
        
        // CONF_PATH是文件系统路径，不能传给Bd_Conf
        define('CONF_PATH', ROOT_PATH . 'conf');
        
        //app目录
        define('APP_PATH',  ROOT_PATH . 'app');
        
        //libs 公共类库目录
        define('LIB_PATH',  ROOT_PATH . 'libs');
    
        //webroot 项目根目录和静态文件目录
        define('WEB_ROOT',  ROOT_PATH . 'webroot');

        return true;
    }

    private static function getAppName()
    {
        
        $app_name = null;
       
        // cgi
        if(PHP_SAPI != 'cli')
        {
            //某些重写规则会导致"/openapi/index.php/"这样的SCRIPT_NAME
            $script = explode('/', trim($_SERVER['SCRIPT_FILENAME'], '/'));
            
            // 注意这里 跟你的配置有关系如：[SCRIPT_FILENAME] => /var/htdocs/levin/yaf/webroot/openapi/index.php
            if(count($script) == 7 && $script[6] == 'index.php')
            {
                $app_name = $script[5];
            }
        }
        // cli
        else
        {
            $file = $_SERVER['argv'][0];
            if($file{0} != '/')
            {
                $cwd = getcwd();
                $full_path = realpath($file);
            }
            else
            {
                $full_path = $file;
            }
            if(strpos($full_path, APP_PATH) === 0)
            {
                $s = substr($full_path, strlen(APP_PATH)+1);
                if(($pos = strpos($s, '/')) > 0)
                {
                    $app_name = substr($s, 0, $pos);
                }
            }
        }
        return $app_name;
    }

    private static function initAppEnv($app_name)
    {
        // 检测当前App
        if($app_name != null || ($app_name = self::getAppName()) != null)
        {
            define('IS_ODP', true);
            define('MAIN_APP', $app_name);
        }
        else
        {
            define('IS_ODP', false);
            define('MAIN_APP', 'unknown-app');
        }
        
        // APP宏仅为了兼容一些老代码
        define('APP', MAIN_APP);

        // 设置当前App
        require_once LIB_PATH . '/bd/AppEnv.php';
        Bd_AppEnv::setCurrApp(MAIN_APP);

        return true;
    }

    // 初始化Ap
    private static function initYaf()
    {
        // 读取App的ap框架配置
        require_once LIB_PATH . '/bd/Conf.php';
        
        $conf = Bd_Conf::getConf('application/product');
        
        // 设置代码目录，其他使用默认或配置值
        $conf['application']['directory'] = Bd_AppEnv::getEnv('code');
        
        // 生成yaf实例
        $app = new Yaf_Application($conf);
        
        return true;
    }
}
