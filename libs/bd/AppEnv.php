<?php
/**
* brief of AppEnv.php:
* 
* 提供APP相关的上下文信息，主要是各种路径信息
*
* @author Levin <levin@chope.co> 
* @date 2011/12/21 15:58:58
* @version $Revision: 1.1 $ 
* @todo 
*/

final class Bd_AppEnv
{
    private static $app;
    private static $arrEnv;

    /*
     * 设置当前App，返回前一个App，不传参数时，会设为主App
     * */
    public static function setCurrApp($app = null)
    {
        $strPrevApp = self::$app;
        self::$app = empty($app)?MAIN_APP:$app;
        return $strPrevApp;
    }

    public static function getCurrApp()
    {
        return self::$app;
    }

    /*
     * 获取当前或参数指定App的上下文环境值
     *
     * 预定义列表：conf - App的配置路径，供Bd_Conf使用
     *             data - App的数据根目录
     *             code - App的代码根目录
     *             template -获取templat的根目录
     * */
    public static function getEnv($key, $app = null)
    {
        $app = empty($app) ? self::$app : $app;

        switch($key)
        {
            case 'conf':
                return CONF_PATH . "/{$app}";

            case 'code':
                return APP_PATH . "/{$app}";

            case 'cache':
                return CACHE_PATH . "/{$app}"; 
            case 'template':
		        return APP_PATH . "/{$app}/templates";	
            
            default:
                return self::$arrEnv[$app][$key];
        }
    }

    /*
     * 设置当前或参数指定App的上下文环境值
     *
     * note: 仅可设置非预定义的环境值
     * */
    public static function setEnv($key, $value, $app = null)
    {
        $app = empty($app) ? self::$app : $app;
        self::$arrEnv[$app][$key] = $value;
    }
}
