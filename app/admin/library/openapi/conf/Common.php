<?php

class Openapi_Conf_Common {

    //CLIENT
    const CLIENT_CHOPE        = 'chope'; //专门用于内部前端页面调用的TOKEN 

    const CLIENT_MAINWEBSITE  = 'mainwebsite'; //MainWebsite调用短链接口
   
    const CLIENT_OPENAPI      = 'openapi';
    
    const CLIENT_MR3          = 'mr3';
    
 
    //CLIENT TOKEN
    public static $CLIENT_TOKENS = array (
        self::CLIENT_MAINWEBSITE          => '274c1db8781d22f3b4779239c9d94a3d',
        self::CLIENT_CHOPE                => '81b1c962eb2029720bc1a3ab95690de4', 
        self::CLIENT_OPENAPI              => 'd3cc7bb731aefe19ab80b93df34daf5e',
        self::CLIENT_MR3                  => '9e31dce39090b7f9ec76f95822367bfa',
    );
}
