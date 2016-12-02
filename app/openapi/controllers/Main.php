<?php

/**
 * 基础控制器，其他控制器会继承
 * 
 * @author : Levin Xu < levin@chope.co >
 *
 */

class Controller_Main extends Openapi_Page {

    /**
     * 基础控制器初始化方法
     *
     */
    public function init() {
        parent::init();
    }

    public function indexAction() {
        echo "access denied !";
    }
}


