<?php

/**
 * 短链后台管理员信息表
 * @author: Levin <levin@chope.co>
 * @date: 2016-08-24 15:20
 *
 */

class Dao_Userinfo {
    
    /**
     * Redis  从库实例
     * @var object
     *
     */
    private static $redisSlave = null;

 
    public function __construct() 
    {
        
        if (!self::$redisSlave) {
            self::$redisSlave  = Base_Redis::getInstance();
        }
    }

    /**
     * 根据username获取用户信息
     * @param string $username
     * @return string password 
     */ 
    public function getUserinfoByUsername($username) {
        return self::$redisSlave->hget(Openapi_Conf_Redis::HASH_ADMIN_USER, $username); 
    } 
}
