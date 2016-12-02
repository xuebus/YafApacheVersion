<?php

/**
 * 短链后台管理员信息表
 * @author: Levin <levin@chope.co>
 * @date: 2016-08-24 15:20
 *
 */

class Service_Userinfo {

    
    private static $daoUserinfo = null; 

    public function __construct() {
        if (!self::$daoUserinfo) {
            self::$daoUserinfo = new Dao_Userinfo();
        } 
    }

    /**
     * 根据username获取用户信息
     * @param string $username
     * @return array info
     */ 
    public function getUserinfoByUsername($username) {
        return self::$daoUserinfo->getUserinfoByUsername($username); 
    } 
}
