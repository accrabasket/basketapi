<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql;
use Zend\Db\Sql\Expression;
class customerModel  {
    public $adapter;
    public $sql;
    public function __construct() {
        $this->adapter = new Adapter(array(
            'driver' => 'Mysqli',
            'database' => 'customerbasket',
            'username' => 'root',
            'password' => '',
        ));
        $this->sql = new Sql\Sql($this->adapter);
    }
    
    function getItemIntoCart($optional) {
        try {
            $where = new \Zend\Db\Sql\Where();
            $query = $this->sql->select('cart_item');
            if(!empty($optional['merchant_inventry_id'])) {
                $query = $query->where(array('merchant_inventry_id'=>$optional['merchant_inventry_id']));
            }
            if(!empty($optional['user_id'])){
                $query = $query->where(array('user_id'=>$optional['user_id']));
            }
            if(!empty($optional['guest_id'])) {
                $query = $query->where(array('guest_id'=>$optional['guest_id']));
            }
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $result;
        } catch (\Exception $ex) {
            return false;
        }        
    }
    
    function addToCart($params) {
        try {
            $query = $this->sql->insert('cart_item')
                        ->values($params);
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $this->adapter->getDriver()->getLastGeneratedValue();
        } catch (\Exception $ex) {
            return false;
        } 
    }
    
    function updateCart($params, $where) {
        try {
            $query = $this->sql->update('cart_item')
                        ->set($params)
                        ->where($where);
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return true;
        } catch (\Exception $ex) {
            return false;
        }        
    }
    
    function deleteCart($where) {
        try {
            $query = $this->sql->delete('cart_item')
                        ->where($where);
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return true;
        } catch (\Exception $ex) {
            return false;
        }         
    }
}
