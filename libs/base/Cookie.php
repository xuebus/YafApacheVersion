<?php

class Base_Cookie {

    /**
     * 获取COOKIE的值
     *
     */
    public static function get($key){
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : null; 
    }

    /**
     * 设置COOKIE及过期时间
     * @param string $key
     * @param string $value
     * @param int $expire
     * @param string $path 用来指定cookie被发送到服务器的哪一个目录路径下. 
     * @param string $domain 能够在浏览器端对cookie的发送进行限定. 
     * @param int $secure 表示这个cookie是否通过加密的HTTPS协议在网络上传输. 
     * return void
     */
    public static function set($key, $value, $expire = 86400, $path = '/', $domain = DOMAIN, $secure = 1){
        $expire    = time() + $expire; 
        setcookie($key, $value, $expire, $path);
    }

    /**
     * 设置cookie过期
     * @param string $key
     * @return void
     */
    public static function delete($key) {
        setcookie($key, '', time() - 3600);
    }

}
