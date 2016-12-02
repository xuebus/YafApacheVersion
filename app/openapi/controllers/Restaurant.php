<?php

/**
 * 餐厅信息展示页面
 * @author : Levin <levin@chope.co>
 * @date: 2016-09-23 17:36
 *
 */

class Controller_Restaurant extends Openapi_Page {

    private $serviceRestaurant = null;
  
    public function init(){
        parent::init();
        $this->serviceRestaurant = new Service_Restaurant();
    }

    public function detailAction() {
        
        $restaurant = $this->serviceRestaurant->getRestaurantInfoById(1);
        echo "<pre>";print_r($restaurant);exit;

    }
    
}
