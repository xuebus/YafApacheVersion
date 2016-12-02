<?php

/**
 * @desc  : Service redis
 * @author: Levin <levin@chope.co> 
 * @date  : 2016-07-26
 *
 */

class Base_Redis {

    /**
     * 默认 连接超时
     */
    const DEFAULT_CONNECT_TIMEOUT = 5;
    
    /** 
     * 实例
     * @var array redis 
     */
    private static $_instances = array();

    /** 
     * 实例 id
     * @var string 
     */
    private static $_instanceKey = null;

    public function __construct(){}
    private function __clone(){}
    private function __destruct(){}
   
    /**
     * 获取实例
     * @param array $config
     * @return obj $instance
     */
    public static function getInstance($master = false)
    {
        $config = Base_Config::getConf('redis'); 
        if ($master) {
            $host = isset($_SERVER['DB_REDIS_HOST_W']) ? $_SERVER['DB_REDIS_HOST_W'] : $config['master']['host'];
            $port = isset($_SERVER['DB_REDIS_PORT_W']) ? $_SERVER['DB_REDIS_PORT_W'] : $config['master']['port']; 
        } else {
            $host = isset($_SERVER['DB_REDIS_HOST_R']) ? $_SERVER['DB_REDIS_HOST_R'] : $config['slave']['host'];
            $port = isset($_SERVER['DB_REDIS_PORT_R']) ? $_SERVER['DB_REDIS_PORT_R'] : $config['slave']['port']; 
        } 
        self::$_instanceKey = $host . ':' . $port;
        if (!isset(self::$_instances[self::$_instanceKey]) || (!(self::$_instances[self::$_instanceKey] instanceof Redis) || (self::$_instances[self::$_instanceKey]->ping() !== '+PONG'))) 
        {
            $link = new Redis();
            $link->connect($host, $port, self::DEFAULT_CONNECT_TIMEOUT);
            self::$_instances[self::$_instanceKey] = $link; 
        }
        return self::$_instances[self::$_instanceKey];
    }

    /**
     * Set 操作，string类型
     * @param string $key
     * @param string $value
     * @param int $ttl Key的剩余生存时间 
     * @return mixed $ret
     */  
    public function set($key, $value, $ttl = null)
    {
        if (is_int($ttl)) {
            return self::$_instances[self::$_instanceKey]->setex($key, $ttl, $value);
        }
        return self::$_instances[self::$_instanceKey]->set($key, $value);
    } 

    /**
     * 获取RedisKey的剩余生存时间 int(60) 表示60s
     * @param string $key
     *
     */
    public function ttl($key) {
        return self::$_instances[self::$_instanceKey]->ttl($key);
    }

    /**
     * Get 操作，string类型
     * @param string $key
     * @return mixed
     */ 
    public function get($key)
    {
        return self::$_instances[self::$_instanceKey]->get($key);
    } 

    /**
     * 批量设置
     * @param type $group
     * @return mixed
     */
    public function mset($group) {

        return self::$_instances[self::$_instanceKey]->mset($group);
    }

    /**
     * 批量获取
     * @param string $keys
     * @return mixed
     */
    public function mget($keys) {

        $values = self::$_instances[self::$_instanceKey]->mGet($keys);
        return array_combine($keys, $values);
    }

    /**
     * incr 操作，string类型
     * @param string $key
     * @return mixed 
     */ 
    public function incr($key)
    {
        return self::$_instances[self::$_instanceKey]->incr($key);
    }

    /**
     * incrBy 操作，string类型
     * @param string $key
     * @param int $step  自增步长
     * @return mixed $ret
     */ 
    public function incrBy($key, $step)
    {
        return self::$_instances[self::$_instanceKey]->incrBy($key, $step);
    }

    /**
     * hash 设置
     * @param type $group
     * @return mixed
     */
    public function hset($key, $hashKey, $value) {

        return self::$_instances[self::$_instanceKey]->hSet($key, $hashKey, $value);
    }

    /**
     * hash 获取
     * @param string $keys
     * @return mixed
     */
    public function hget($key, $hashKey) {
        return self::$_instances[self::$_instanceKey]->hGet($key, $hashKey); 
    }
    
    /**
     * hash 批量获取
     * @param string $key
     * @param type $hashKeys
     * @return mixed
     */
    public function hmget($key, $hashKeys) {
        return self::$_instances[self::$_instanceKey]->hMGet($key, $hashKeys);
    }

    /**
     * hash 批量设置
     * @param string $key
     * @param type $hashKeys
     * @return bool
     */
    public function hmset($key, $group) {
        return self::$_instances[self::$_instanceKey]->hmset($key, $group);
    }

    /** 
     * hash 判断一个field是否存在 
     * @param string $key
     * @param string $field 
     * @return mixed
     */
    public function hExists($key, $field) {
        return self::$_instances[self::$_instanceKey]->hExists($key, $field);
    }
    
    /**
     * hash 获取全部键名
     * @param string $keys
     * @return mixed
     */
    public function hkeys($key) {

        return self::$_instances[self::$_instanceKey]->hKeys($key);
    }
    
    /**
     * hash 获取全部值
     * @param string $keys
     * @return mixed
     */
    public function hgetAll($key) {

        return self::$_instances[self::$_instanceKey]->hGetAll($key);
    }

    /**
     * hash 自增
     * @param string $key
     * @param string $hashKey
     * @param int $value 自增的值，可以为负值
     * @return mixed
     */
    public function hIncrBy($key, $hashKey, $value) {

        return self::$_instances[self::$_instanceKey]->hIncrBy($key, $hashKey, $value);
    }

    /**
     * 删除指定 key
     * @param string $key
     * @return mixed
     */
    public function delete($key) {

        return self::$_instances[self::$_instanceKey]->delete($key);
    }    

    /**
     * 向列表头部插入一个元素
     * @param string $key
     * @return int 列表长度
     */
    public function lpush($key, $value) {

        return self::$_instances[self::$_instanceKey]->lPush($key, $value);
    }

    /**
     * 返回 list 的第一个元素
     * @param string $key
     * @return string/bool value/false
     */
    public function lpop($key) {

        return self::$_instances[self::$_instanceKey]->lPop($key);
    }

    /**
     * 向列表尾部插入一个元素
     * @param string $key
     * @return int 列表长度
     */
    public function rpush($key, $value) {

        $ret = self::$_instances[self::$_instanceKey]->rPush($key, $value);
        if (false === $ret) {
            Base_Log::warning('redis rpush failed', Openapi_Conf_ErrorCode::REDIS_SET_FAILED, array($key, $value));
            return false;
        }
        return $ret;
    }

    /**
     * 返回 list 的最后一个元素
     * @param string $key
     * @return string/bool value/false
     */
    public function rpop($key) {
        $ret = self::$_instances[self::$_instanceKey]->rPop($key);
        if (false === $ret) {
            Base_Log::warning('redis rpop failed', Openapi_Conf_ErrorCode::REDIS_SET_FAILED, array($key));
            return false;
        }
        return $ret;
    }


    /**
     * 返回列表中指定位置的数据
     * @param string $key
     * @param int $index
     * @return mixed
     */
    public function lget($key, $index) {

        return self::$_instances[self::$_instanceKey]->lGet($key, $index);
    }

    /**
     * 返回列表中某个范围内的数据
     * @param string $key
     * @param int $start
     * @param int $stop
     * @return mixed
     */
    public function lrange($key, $start, $stop) {
        return self::$_instances[self::$_instanceKey]->lRange($key, $start, $stop);
    }

    /**
     * 返回列表长度
     * @param string $key
     * @return int 
     */
    public function llen($key) {
        return self::$_instances[self::$_instanceKey]->lLen($key);
    }

    /**
     * 移除列表中的某些元素
     * @param string $key
     * @param mixed $value
     * @param int $count
     * @return bool
     */
    public function lrem($key, $value, $count = 0) {
        return self::$_instances[self::$_instanceKey]->lRem($key, $value, $count);
    }

    /**
     * 向集合中插入一个元素
     * @param string $key
     * @return long 集合中元素个数
     */
    public function sadd($key, $value) {

        $ret = self::$_instances[self::$_instanceKey]->sAdd($key, $value);

        if (false === $ret) {
            Base_Log::warning('redis sadd failed: this value is already in the set', Openapi_Conf_ErrorCode::REDIS_SET_FAILED, array($key, $value));
            return false;
        }

        return $ret;
    }

    /**
     * 返回集合中所有的成员
     * @param string $key
     * @return mixed
     */
    public function smembers($key) {

        return self::$_instances[self::$_instanceKey]->sMembers($key);
    }

    /**
     * 检查member是否是集合中的成员
     * @param string $key
     * @param string $member
     * @return mixed
     */
    public function sismember($key, $member) {

        return self::$_instances[self::$_instanceKey]->sIsMember($key, $member);
    }

    /**
     * 向redis ShortedSet中的元素添加分数
     * @param string $key
     * @param int $score
     * @param string $member
     * @return mixed
     */
    public function zincrby($key, $score, $member) {

        $ret = self::$_instances[self::$_instanceKey]->zIncrBy($key, $score, $member);
        if (false === $ret) {
            Base_Log::warning('redis zincrby failed', Openapi_Conf_ErrorCode::REDIS_SET_FAILED, array($key, $score, $member));
            return false;
        }
        return $ret;
    }

    /**
     * 设置redis key的过期时间
     * @param string $key
     * @param int $ttl
     * @return mixed
     */
    public function expire($key, $ttl) {

        $ret = self::$_instances[self::$_instanceKey]->expire($key, $ttl);
        if (false === $ret) {
            Base_Log::warning('set key expire time failed', Openapi_Conf_ErrorCode::REDIS_SET_FAILED, array($key, $ttl));
            return false;
        }
        return $ret;
    }

    /**
     * 返回有序集合成员的个数
     * @param string $key
     * @return mixed
     */
    public function zcard($key) {
        return self::$_instances[self::$_instanceKey]->zCard($key);
    }

    /**
     * 删除集合中的成员
     * @param string $key
     * @return mixed
     */
    public function zrem($key, $member) {
        return self::$_instances[self::$_instanceKey]->zRem($key, $member);
    }

    /**
     * 返回有序集合成员的分数
     * @param string $key
     * @param string $member
     * @return mixed
     */
    public function zscore($key, $member) {
        return self::$_instances[self::$_instanceKey]->zScore($key, $member);
    }

    /**
     * 返回有序集合成员排名
     * @param string $key
     * @param string $member
     * @return mixed
     */
    public function zrevrank($key, $member) {
        return self::$_instances[self::$_instanceKey]->zRevRank($key, $member);
    }

    /**
     * 取出SortedSet范围内的元素
     * @param string $key
     * @param int $start
     * @param int $stop
     * @param int $withscores
     * @return mixed
     */
    public function zrevrange($key, $start, $stop, $withscores) {
        return self::$_instances[self::$_instanceKey]->zRevRange($key, $start, $stop, $withscores);
    }

    /**
     * 取出SortedSet范围内的元素
     * @param string $key
     * @param int $max
     * @param int $min
     * @param int $appends
     * @return mixed
     */
    public function zrevrangebyscore($key, $max, $min, $appends) {
        return self::$_instances[self::$_instanceKey]->zRevRangeByScore($key, $max, $min, $appends);
    }

    
}
