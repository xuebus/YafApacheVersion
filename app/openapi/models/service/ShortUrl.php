<?php

/**
 * Service ShortUrl 短链服务
 * @author : Levin <levin@chope.co>
 * @date: 2016-08-12 10:25
 *
 */

class Service_ShortUrl {

    private static $daoShortUrl = null;

    /**
     * 初始化 
     *
     */
    public function __construct() {
        if (!self::$daoShortUrl) {
            self::$daoShortUrl = new Dao_ShortUrl();
        }
    }

    /** 
     * 用短ID获取长链获，直接通过redis返回
     * @param int $id
     * @return string $url
     *
     */ 
    public function getLongUrlById($id) {
        return self::$daoShortUrl->getLongUrlById($id); 
    }

    /**
     * 短链生成
     * @param string $url 
     * @return object
     *
     */
    public function shorten($url) {
        $id = self::$daoShortUrl->getShortUrlId($url);  
        
        //短链不存在
        if (empty($id)) {
            $id = self::$daoShortUrl->getNextId();
            if (intval($id) <= 0) {
                Base_Log::warning('allocate id failed', Openapi_Conf_ErrorCode::ERROR_REDIS_SET_FAILED, array('id' => $id, 'url' => $url));
                return false;
            }
            //写入数据库Redis 
            $ret = self::$daoShortUrl->shorten($url, $id); 
            if($ret === false) {
                Base_Log::warning('craete short url failed', Openapi_Conf_ErrorCode::ERROR_REDIS_SET_FAILED, array('id' => $id, 'url' => $url));
                return false;
            }
        }
        $result = array(
            'id'        => intval($id),
            'url_short' => $_SERVER['CHOPE_SHORT_URL'] . Base_Common::alphaId($id),
            'url_long'  => $url,
            'status'    => empty($id) ? false : true,
        );
        return $result;
    }

    /**
     * 生成自定义短链
     * @param string $url
     * @param int $id
     * @return object
     */
    public function custom($url, $customId) {
        //判断短链是否已经存在，存在则直接返回
        $id = self::$daoShortUrl->getShortUrlId($url);
        if ($id) return $this->_response($url, $id); 
            
        //如果短链不存在， 且不需要定制关键词短链
        if (empty($customId)) {
            $id = self::$daoShortUrl->getNextId();
            if (intval($id) <= 0) {
                Base_Log::warning('allocate id failed', Openapi_Conf_ErrorCode::ERROR_REDIS_SET_FAILED, array('id' => $id, 'url' => $url));
                return false;
            }
            //写入数据库Redis 
            $ret = self::$daoShortUrl->shorten($url, $id); 
            if($ret === false) {
                Base_Log::warning('craete short url failed', Openapi_Conf_ErrorCode::ERROR_REDIS_SET_FAILED, array('id' => $id, 'url' => $url));
                return false;
            }
            return $this->_response($url, $id); 
        } 
        //短链不存在，且需要定制关键词短链
        else {
            //检测ID是否已经被使用过了
            $ret = self::$daoShortUrl->checkHashIdExists($customId);
            if ($ret) {
                Base_Log::warning('id have exists, should be custom id', Openapi_Conf_ErrorCode::ERROR_REDIS_SET_FAILED, array('id' => $customId, 'url' => $url));
                return 10001;
            }
            
            //创建短链映射
            $shortRet =  self::$daoShortUrl->shorten($url, $customId); 
            if (!$shortRet) {
                Base_Log::warning('create short url failed', Openapi_Conf_ErrorCode::ERROR_REDIS_SET_FAILED, array('id' => $customId, 'url' => $url));
                return false;
            }
            
            return $this->_response($url, $customId); 
        } 
    }

    private function _response($url, $id) {
        $result = array(
            'id'        => intval($id),
            'url_short' => $_SERVER['CHOPE_SHORT_URL'] . Base_Common::alphaId($id),
            'url_long'  => $url,
            'status'    => empty($id) ? false : true,
        );
        return $result;

    }
}
