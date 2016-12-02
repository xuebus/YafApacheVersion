<?php

class Openapi_Conf_Redis {
    
    //HASH KEY 计数器HASH KEY
    const HASH_ID_ALLOCATE    = 'allocate';

    //HASH FIELD 短链自增ID
    const FIELD_SHORT_URL_ID  = 'short_url_id'; 
    
    //HASH KEY 短 -> 长,  field 是 ID value 是 long url
    const HASH_LONG_URL       = 'longurl';
    
    //HASH KEY 长 -> 短, Field 是 longurl Value是ID 
    const HASH_SHORT_URL      = 'shorturl'; 

    const HASH_ADMIN_USER     = 'adminuser';

    const SET_CUSTOM_ID       = 'customid';
}
