<?php

class Openapi_Util {

    
    /**
     * 判断是否命中敏感词
     * @param string $idString 
     * @return boolean true 命中 | false 未命中
     *
     */
    public static function hitSensitiveKeywords($idString) {
    
        $idString = strtolower($idString);
            
        $keyworsArr = Openapi_Conf_Keywords::$KEYWORDS; 
        
        foreach($keyworsArr as $keyword) {
            if (false !== strpos($idString, $keyword)) {
                return true; //命中了敏感词!!!
            } 
        }
        return false; 
    }
}
