<?php

/**
 * 短链服务基础控制器
 * @author : Levin <levin@chope.co>
 * @date: 2016-08-12
 *
 */

class Controller_Index extends Openapi_Page {
   
    private $shortUrlService = null;

    public function init(){
        parent::init();
        $this->shortUrlService = new Service_ShortUrl();
    }

    /**
     * 短链解析服务，由短链解析长链，并完成跳转
     * @param void
     * @return mixed
     *
     */
    public function indexAction() {
         
        $idString = trim($this->arrInput['url']); 
  
        $id = Base_Common::alphaId($idString, true);

        $url = $this->shortUrlService->getLongUrlById($id); 
        
        if (empty($url)) {
            Base_Log::warning('short url not exist', Openapi_Conf_ErrorCode::ERROR_SYSTEM, $id); 
            return false;
        }
        
        Header("HTTP/1.1 301 Moved Permanently");
        Header("Location: {$url}");
        exit();
    }

}
