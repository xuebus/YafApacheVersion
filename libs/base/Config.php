<?php

/**
 * Yaf框架 相关的一些工具封装例如：Request/Config等
 *
 *
 */

class Base_Config {

    public static $config = null;

    
    /**
     * 返回框架的CGI参数，包括post参数和query部分的get参数
     * @param void
     * @return mixed
     */
    public function getCgi() {
        if (empty(self::$config['cgi'])) {
            $request = new Yaf_Request_Http();
            $post = $request->getPost();
            $get = $request->getQuery();
            self::$config['cgi'] = array_merge($post, $get);
            foreach (self::$config['cgi']  as $key => $value) {
                
            }
        }
        return self::$config['cgi'];
    }

    /**
     * 根据$conf文件位置，和$section获取配置信息数组，如：$conf = 'redis/subdir/product'; 
     * 具体信息，可以查看APP_PATH /conf/db.ini  对比查看
     * @param string $conf 配置文件路径及section信息
     * @return array
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
}
