<?php

/**
 * @desc app的路由队则，采用Regex的路由正则协议，匹配玩所有的路由规则完了之后，才会走默认的_default Yaf_Route_Static路由
 * 注： 路由注册的顺序很重要, 最后注册的路由协议, 最先尝试路由, 这就有个陷阱. 请注意.
 *
 * @name Yaf Bootstrap
 * @author Levin Xu < levin@chope.co >
 */
class Bootstrap extends Yaf_Bootstrap_Abstract {

    /** 
     * 注册自己的路由协议
     * @param Yaf_Dispatcher $dispatcher
     */
    public function _initRoute(Yaf_Dispatcher $dispatcher) {

        $router = $dispatcher->getRouter();

        // 默认路由, 意思就是凡是没有匹配到regex的请求，都会被路由到这里，而不是使用默认的_default Yaf_Route_Static路由, 暂时没有做处理，我们使用Yaf异常处理机制，应对没有匹配成功的路由
        $defaultRoute = new Yaf_Route_Regex(
             '#^(.*)#',
             array(
                 'controller' => 'Main',
                 'action' => 'index'
             )   
        ); 
                
        $shortRoute = new Yaf_Route_Regex(
            '#^/([0-9a-zA-Z]+)/?$#',
            array(
                'controller' => 'Index',
                'action' => 'index'
            )
        );
   
        $router->addRoute('shortRoute', $shortRoute);
    }
    
    public function _initConfig() {
        $config = Yaf_Application::app()->getConfig();
        Yaf_Registry::set("config", $config);
    }
   
    public function _initLoader($dispatcher) {

        //当前app的library下的目录
        Yaf_Loader::getInstance()->registerLocalNameSpace(array("Openapi"));
        
        //注册自己的自动加载函数
        spl_autoload_register(array('Base_Autoloader', 'loader')); 
    }
 
    /**
     * 注册自己的view控制器，例如smarty
     * @param Ap_Dispatcher $dispatcher
     */
    public function _initView(Yaf_Dispatcher $dispatcher) {

        //禁止ap自动渲染模板
        $dispatcher->disableView();
    }

    /**
     * 插件初始化
     *
     */   
    public function _initPlugin(Yaf_Dispatcher $dispatcher) {
        //todo
    }
 
    public function _initDefaultName(Yaf_Dispatcher $dispatcher) {
        $dispatcher->setDefaultModule("Index")->setDefaultController("Main")->setDefaultAction("index");
        
        //Yaf 框架异常捕获，app/controllers/Error.php
        Yaf_Dispatcher::getInstance()->catchException(true); 
    }
}

/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
