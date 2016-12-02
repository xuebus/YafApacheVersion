<?php

/**
 * 短链数据库基类，数据库相关操作，Mysql/Redis
 * 
 * @author: Levin <levin@chope.co>
 * @date:  2016-08-11 18:24
 *
 */

class Dao_ShortUrl {

    /**
     * Redis  主库实例
     * @var object
     *
     */
    private static $redisMaster = null;
   
    /**
     * Redis  从库实例
     * @var object
     *
     */
    private static $redisSlave = null;

 
    public function __construct() 
    {
        if (!self::$redisMaster) {
            self::$redisMaster = Base_Redis::getInstance(true);
        }
        if (!self::$redisSlave) {
            self::$redisSlave  = Base_Redis::getInstance();
        }
    }

    /**
     * 用短ID获取长链获 HASH id -> url
     * @param int $id
     * @return string $url
     *
     */
    public function getLongUrlById($id) {
        for ($i = 1; $i <= 5; $i++) {
            $url = self::$redisSlave->hget(Openapi_Conf_Redis::HASH_LONG_URL, $id);
            if ($url) break;
        }
        return $url;
    }

    /**
     * 根据长链获取短链 md5(url) -> id 
     * @param string $url
     * @return int $id
     *
     */
    public function getShortUrlId($url) {
        return self::$redisMaster->hget(Openapi_Conf_Redis::HASH_SHORT_URL, md5($url));
    }

    /**
     * 判断自定义短链对应的ID是否已经存在id->url, 判断key是否存在,判断长链是否已经存在 
     *  
     * 1) 获取自增ID的时候检测是否需要跳过 2) 创建自定义ID的时候检测是否已经被使用
     *
     * @param int $id
     * @return boolean false | true
     *
     */
    public function checkHashIdExists($id) {
        $ret = self::$redisMaster->hExists(Openapi_Conf_Redis::HASH_LONG_URL, $id);
        //不存在域或者key
        if ($ret) {
            Base_Log::warning('Field ID  or value long url exists ', Openapi_Conf_ErrorCode::ERROR_REDIS_SET_FAILED, array('id' => $id));
            return true;
        }
        return false;
    }


    /**
     * 分配下一个可用ID
     * @return int 
     *
     */
    public function getNextId(){
        for ($i = 1; $i<=10; $i++){
            $id = self::$redisMaster->hIncrBy(Openapi_Conf_Redis::HASH_ID_ALLOCATE, Openapi_Conf_Redis::FIELD_SHORT_URL_ID, 1);

            //获取ID失败则跳过重试
            if (empty($id)) {
                Base_Log::warning('id allocate failed', Openapi_Conf_ErrorCode::ERROR_REDIS_SET_FAILED, array('id' => $id));
                continue;
            }

            //跳过命中敏感词的ID
            $keyword = Base_Common::alphaId($id); //转成短链，过滤敏感词 
            if (Openapi_Util::hitSensitiveKeywords($keyword)) {
                Base_Log::warning('hit sensitive word', Openapi_Conf_ErrorCode::ERROR_REDIS_SET_FAILED, array('id' => $id));
                continue;
            }
    
            //跳过已经存在的自定义ID        
            if ($this->checkHashIdExists($id)) {
                Base_Log::warning('id exists, should be custom id', Openapi_Conf_ErrorCode::ERROR_REDIS_SET_FAILED, array('id' => $id));
                continue;
            } 
    
            //ID分配成功，且没有命中敏感词，且不在自定义ID集合中（在自定义集合中|命中敏感词的ID，都需要跳过）
            //if ($id > 0 && !Openapi_Util::hitSensitiveKeywords($keyword) && !$customFlag) return $id;
            
            return $id;
        }
        Base_Log::warning('redis get next it failed', Openapi_Conf_ErrorCode::ERROR_REDIS_SET_FAILED, array('id' => $id)); 
        return false;
    }

    /**
     * 短链生成映射
     * @param string $url
     * @param int $id
     *
     */
    public function shorten($url, $id) {
        for ($i = 0; $i < 5; $i++){
            $ret = self::$redisMaster->hset(Openapi_Conf_Redis::HASH_LONG_URL, $id, $url); 
            if ($ret === 1 || $ret === 0) {
                 break;
            }     
        }
        if ($ret !== 1 && $ret !== 0) {
            Base_Log::warning('redis hash key idurl hset failed', Openapi_Conf_ErrorCode::ERROR_REDIS_SET_FAILED, array('ret' => $ret, 'id' => $id, 'url' => $url));
            return false;
        }
        
        //设置md5(长链)->短链ID的映射 
        $field = md5($url);
        for ($i = 0; $i < 10; $i++){
            $ret = self::$redisMaster->hset(Openapi_Conf_Redis::HASH_SHORT_URL, $field, $id);
            if ($ret === 1 || $ret === 0) break;
        }
        if ($ret !==1 && $ret !== 0) {
            Base_Log::warning('redis hash key urlid hset failed', Openapi_Conf_ErrorCode::ERROR_REDIS_SET_FAILED, array('ret' => $ret, 'id' => $id, 'url' => $url));
        }

        return true; 
    }
}
