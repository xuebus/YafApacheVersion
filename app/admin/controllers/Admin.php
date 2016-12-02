<?php

/**
 * 短链服务管理后台
 * @author : Levin <levin@chope.co>
 * @date: 2016-08-12
 *
 */

class Controller_Admin extends Openapi_Page {
  
    private $serviceUserinfo = null;
    private $serviceShortUrl = null;

 
    public function init(){
        parent::init();
        $this->serviceShortUrl = new Service_ShortUrl();
        $this->serviceUserinfo = new Service_Userinfo();
    }

    /**
     * 登陆页面
     *
     */
    public function loginAction() {
            
        $this->display('login.html');       
    }

    /**
     * 信息展示页面
     *
     */
    public function indexAction() {
        $username = $this->_checkStatus();

        if ($username == false) {
            $url = '/mis/admin/login';
            header("Location:{$url}");
        }
        $this->setView('username', $username);
        $this->display('index.html');
    }

    /**
     * 登陆处理
     *
     */
    public function doLoginAction() {

        $username = trim($this->arrInput['username']);    
        $password = trim($this->arrInput['password']); 
   
        $userpass = $this->serviceUserinfo->getUserinfoByUsername($username);
        //登陆成功
        if (!empty($userpass) && md5($password) == $userpass) {
            //设置cookie，并跳转
            Base_Cookie::set('auth', Base_Common::authCode($username));
            $url = '/mis/admin/index';
        } else {
            $url = '/mis/admin/login';
        }
        
        header("Location:{$url}"); 
    }

    /**
     * 登陆状态监测
     * @return boolean false | string $username
     *
     */
    private function _checkStatus(){
        $auth = Base_Cookie::get('auth');
        if (empty($auth)) {
            return false;
        }
        $username = Base_Common::authCode($auth, 'DECODE');
        return $username;
    }
}

