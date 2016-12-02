<?php

/**
 * Chope Short Url 错误码
 * @author: Levin <levin@chope.co>
 *
 */

class Openapi_Conf_ErrorCode {

    const SUCCESS_OK                       = 200;   // SUCCESS 
    const ERROR_SYSTEM                     = 10000; //系统错误
    const ERROR_PARAMS                     = 10001; //参数错误 
    const ERROR_SHORT_URL_NOT_EXIST        = 10002; // 短链不存在 
    const ERROR_REDIS_SET_FAILED           = 10003; // redis设置失败
    const ERROR_SENSITIVE_WORD             = 10004; // 敏感词错误

    public static $ERRMSG = array(
        self::SUCCESS_OK                 => 'ok',
        self::ERROR_SYSTEM               => 'system error',
        self::ERROR_PARAMS               => 'param error',
        self::ERROR_SHORT_URL_NOT_EXIST  => 'short url not exist',
        self::ERROR_REDIS_SET_FAILED     => 'redis set failed', 
        self::ERROR_SENSITIVE_WORD       => 'Hit the sensitive word.',
    );

    
}
