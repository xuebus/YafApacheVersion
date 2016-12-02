<?php

/**
 * 餐厅model.
 * @author: Levin <levin@chope.co>
 * @date: 2016-09-23 17:35
 *
 */

class Service_Restaurant { 

    private static $daoRestaurant = null;

    public function __construct() {
        if(!self::$daoRestaurant) {
            self::$daoRestaurant = new Dao_Restaurant();
        }
    }
    
    /**
     * 获取餐厅信息
     *
     */
    public function getRestaurantInfoById($id) {
       return self::$daoRestaurant->getRestaurantInfoById($id); 
    }    
}
