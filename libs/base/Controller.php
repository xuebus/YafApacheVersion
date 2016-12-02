<?php

class Base_Controller extends Yaf_Controller_Abstract{

    //cgi参数容器
    public $arrInput = array();

    /**
     * 
     * 模板变量 
     */
    protected $view = array();


    public function init() {
        $params = $this->getRequest()->getParams(); //route 配置的参数
        $post = $this->getRequest()->getPost(); //全部的post参数
        $get = $this->getRequest()->getQuery(); //全部的get参数 
        $this->arrInput = array_merge($params, $post, $get);
        foreach ($this->arrInput  as $key => $value) {
            $this->arrInput[$key] = htmlspecialchars(trim($this->arrInput[$key]), ENT_QUOTES);
        }
    }

    /**
     *
     * 设置模版变量
     * @param string $key  模板变量名
     * @param mixed $value 模板变量值
     */
    protected function setView($key, $value) {
        $this->view[$key] = $value;
    }

    /**
     *
     * 显示模版
     * @param string $tplFile
     * @return 
     */
    protected function display($tplFile, $cacheId=null) {
        echo $this->fetch($tplFile, $cacheId);
    }

    /**
     *
     * 返回解析内容
     * @param string $tplFile
     * @return html
     */
    protected function fetch($tplFile, $cacheId) {
        $tpl = new Base_View();
        $tpl->assign($this->view);
        return $tpl->fetch($tplFile, $cacheId);
    }

    protected function clearCache($tplFile, $cacheId) {
        $tpl = new Base_View();
        $tpl->clearCache($tplFile, $cacheId);
    }

    /**
     * 
     * 重定向后改变url
     * @param string $url 指定的url
     */
    protected function redirectTo($url) {
        header('Location: '.$url);
        exit();
    }
} 
