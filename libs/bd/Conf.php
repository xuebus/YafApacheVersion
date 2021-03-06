<?php
/***************************************************************************
 * 
 * Copyright (c) 2011 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/
 
/**
 * @file Conf.php
 * @author Levin(levin@chope.co)
 * @date 2016/09/10 12:42:33
 *  
 */

class Bd_Conf 
{
    protected static $set_conf = null;
    
    /* 
     * 获取配置
     * @param string $item: 指定配置项，表示获取全部配置
     *
     */
    public static function getConf($conf) {
       $arr = explode('/', $conf);
        if (count($arr) == 1) {
            $section = $conf;
            $filename = "{$conf}.ini";
        } else {
            $section = array_pop($arr);
            $filename = implode('/', $arr) . '.ini'; 
        }
        $config = new Yaf_Config_Ini(ROOT_PATH . '/conf/' . $filename, $section);
        return $config->toArray();
 

    }
    
    /* 
     * 设置配置项，调试时使用。下次getConf时可获取这里设置的值
     *
     *  @param:
     *      $item: 指定配置项，表示获取全部配置
     *      $value: 指定要设置成的值
     *
     *  @return void
     */
    public static function setConf($item, $value)
    {
        // setLevel
        if ($item[0] != '/') {
            $item = self::getLevel() . "/$item";
        }

        // root has to be an array
        if (($item == '' || $item == '/') && !is_array($value)) {
            return;
        }

        // parse query string
        $query = explode('/', $item);

        // build array
        $built = $value;
        while (($node = array_pop($query)) !== null) {
            if ($node === '') continue;
            $built = array($node => $built);
        }

        // merge with global set_conf
        if (self::$set_conf == null) {
            self::$set_conf = $built;
        } else {
            self::$set_conf = self::my_array_merge_recursive(self::$set_conf, $built);
        }

    }
    
    /* 
     * 获取当前App或指定App的配置
     *
     *  @param:
     *      $item: 指定子配置项，传null时表示获取全部配置
     *      $app: 指定App名称，传null时，表示采用当前App 
     *
     *  @return
     *      array: 成功
     *      string: 成功
     *      false: 失败
     */
    public static function getAppConf($item = null, $app = null)
    {
        //和ODP环境解耦
        if(!defined('IS_ODP')){
            return self::getConf($item);
        }
        $conf_path = Bd_AppEnv::getEnv('conf', $app);

        if(!empty($item))
        {
            $conf_path .= "/$item";
        }

        return self::getConf($conf_path);
    }

    public static function getMyConf($item = null, $app = null)
    {
        return self::getAppConf($item, $app);
    }

    // helper functions
    private static function my_array_merge_recursive() {
        $arrays = func_get_args();
        $base = array_shift($arrays);

        foreach ($arrays as $array) {
            reset($base); //important
            while (list($key, $value) = @each($array)) {
                if (is_array($value) && @is_array($base[$key])) {
                    $base[$key] = self::my_array_merge_recursive($base[$key], $value);
                } else {
                    $base[$key] = $value;
                }
            }
        }
        return $base;
    }

}


/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
