<?php

/**
 * 餐厅信息
 * @author: Levin <levin@chope.co>
 * @date: 2016-09-23 18:27
 *
 */

class Dao_Restaurant extends Openapi_Dao_Base {
    
    /**
     * 订单信息
     *
     */
    private static $restaurant = 'restaurant_info';

   
    /**
     * 获取餐厅信息 
     * @param int $id
     * @return mixed
     *
     */ 
    public function getRestaurantInfoById($id) {
        $sql = "select * from " . self::$restaurant . " where id=?";
        return $this->getRow($sql, array($id));
    }
}
