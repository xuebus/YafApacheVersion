<?php

/**
 * 异常主动捕获，如果没有异常捕获，例如在action不存的时候会报php fatal error
 * @author : Levin <levin@chope.co>
 *
 */

class Controller_Error extends Openapi_Page {
   
    /**
     * 错误处理
     *
     */ 
    public function errorAction($exception) {  
        
        Yaf_Dispatcher::getInstance()->disableView();  
        /* error occurs */  
        switch ($exception->getCode()) {  
            case YAF_ERR_NOTFOUND_MODULE:  
            case YAF_ERR_NOTFOUND_CONTROLLER:  
            case YAF_ERR_NOTFOUND_ACTION:  
            case YAF_ERR_NOTFOUND_VIEW:  
                Base_Log::warning($exception->getMessage(), 404, array());
                Base_Message::showError('Yaf Exception', array(), 404);
                break;  
            default :  
                $message = $exception->getMessage();  
                Base_Log::warning($exception->getMessage(), 0, array());
                Base_Message::showError('Yaf Exception', array(), 500); 
                break;  
        }  
    }  
}
