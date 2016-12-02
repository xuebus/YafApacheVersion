<?php

class Base_Session {
    
    protected static $instance = NULL;
    
    private function __construct() {
        return;
    }
	
    public function __init() {
		@session_start();
	}
	
    public static function &getInstance(){
		if( !self::$instance ) {
			self::$instance = new Base_Session;
			self::$instance->__init();
		}
		return self::$instance;
	}
	
    public static function setName() {
		$_this = &Base_Session::getInstance();
		return session_name();
	}
	
    public static function getSid()	{
		$_this = &Base_Session::getInstance();
		return session_id();
	}
	
    public static function get($key = NULL) {
		$_this = &Base_Session::getInstance();
        $val = isset($_SESSION[$key]) ? $_SESSION[$key] : NULL;
        return (NULL === $key)? $_SESSION : $val;
	}
	
    public static function set($key, $value = NULL) {
		$_this = &Base_Session::getInstance();
		if( NULL === $value ) {
			unset($_SESSION[$key]);
		}
		$_SESSION[$key] = $value;
		return TRUE;
	}
	
    public static function destroy() {
		self::$instance = NULL;
		return session_destroy();
	}

}
