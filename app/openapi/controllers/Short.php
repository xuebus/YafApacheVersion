<?php

/**
 * 短链服务基础控制器
 * @author : Levin <levin@chope.co>
 * @date: 2016-08-12
 *
 */

class Controller_Short extends Openapi_Base {
   
    private $shortUrlService = null;
  
    public function init(){
        parent::init();
        $this->shortUrlService = new Service_ShortUrl();
    }

    /**
     * 短链转长链
     * @param void
     * @return json 
     *
     */
    public function expandAction() {

        if (!isset($this->arrInput['url']) || empty($this->arrInput['url'])) {
            Base_Log::warning('params url can not be empty', Openapi_Conf_ErrorCode::ERROR_PARAMS, $this->arrInput['url']);
            Base_Message::showError('params url can not be empty', $this->arrInput['url']);
        }
 
        $url = trim($this->arrInput['url']); 
               
        $url = htmlspecialchars_decode($url);

        $url = urldecode($url);
            
        $arr = explode('/',  trim($url, '/'));
        $keyword = trim($arr[3]);

        $id = Base_Common::alphaId($keyword, true);
        
        $urlLong = $this->shortUrlService->getLongUrlById($id); 
        
        $result = array(
            'id' => $id,
            'url_short' => $url,
            'url_long'  => $urlLong,
            'status'    => empty($urlLong) ? false : true,
        ); 
        if ($urlLong) {
            Base_Message::showSucc('ok', $result);        
        } else {
            Base_Message::showError('error', $result);
        }
    }

    /**
     * 长链转短链
     * @param  string $url : urlencode('http://www.baidu.com/?a=1&b=2'),
     * @return object 
     *
     */
    public function shortenAction() {
        if (!isset($this->arrInput['url']) || empty($this->arrInput['url'])) {
            Base_Log::warning('params error', Openapi_Conf_ErrorCode::ERROR_PARAMS, $this->arrInput);
            Base_Message::showError('params error', $this->arrInput);
        }
        $url = trim($this->arrInput['url']);
        $url = htmlspecialchars_decode($url);
        
        $url = urldecode($url);
 
        $result = $this->shortUrlService->shorten($url);
        if ($result) {
            Base_Message::showSucc('ok', $result); 
        } else {
            Base_Message::showError('error', $result);
        }
    }


    /**
     * 自定义指定ShortUrl地址 
     * @param string $url
     * @param string #keyword
     * @return object
     *
     */
    public function customAction() {
        if (!isset($this->arrInput['url']) || empty($this->arrInput['url'])) {
            Base_Log::warning('params error', Openapi_Conf_ErrorCode::ERROR_PARAMS, $this->arrInput);
            Base_Message::showError('params error', $this->arrInput);
        }
        $url = trim($this->arrInput['url']);
        $url = htmlspecialchars_decode($url);
        $url = urldecode($url);

        $keyword = trim($this->arrInput['keyword']);
        $customId = Base_Common::alphaId($keyword, true);

        //字符串不符合规范
        
        if (!empty($keyword) && (strpos($keyword, 'a') === 0)) {
            Base_Message::showError('error', '', 10002);
        }
 
        $result = $this->shortUrlService->custom($url, $customId); 
        if ($result === 10001) {
            Base_Message::showError('error', $result, 10001);
        } else if ($result) {
            Base_Message::showSucc('ok', $result); 
        } else {
            Base_Message::showError('error', $result);
        }
    } 
}
