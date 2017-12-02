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
            if(!empty($where)) {
                $query = $this->sql->update('cart_item')
                            ->set($params)
                            ->where($where);
                $satements = $this->sql->prepareStatementForSqlObject($query);
                $result = $satements->execute();
                return true;
            }else{
                return false;
            }
        } catch (\Exception $ex) {
            return false;
        }        
    }
    
    function deleteCart($where) {
        try {
            if(!empty($where)) {
                $query = $this->sql->delete('cart_item')
                            ->where($where);
                $satements = $this->sql->prepareStatementForSqlObject($query);
                $result = $satements->execute();
                return true;
            }else {
                return false;
            }
        } catch (\Exception $ex) {
            return false;
        }         
    }
    function getUserDetail($whereParams, $optional = array()) {
        try {
            $where = new \Zend\Db\Sql\Where();
            $orQuery = "";
            $query = $this->sql->select('user_master'); 
            if(!empty($whereParams['id'])) {
                $query = $query->where(array('user_master.id' => $whereParams['id']));
            } 
            if(!empty($whereParams['email'])) {
                $query = $query->where($where->nest->or->equalTo('user_master.email', $whereParams['email']), "OR");
            }            
            if(!empty($whereParams['mobile_number'])){
                $query = $query->Where($where->nest->or->equalTo('user_master.mobile_number', $whereParams['mobile_number']), "OR");
            }  
                      
            if(!empty($whereParams['password'])) {
                $query = $query->where(array('user_master.password' => $whereParams['password']));
            }            
            if(!empty($whereParams['name'])) {
                $query = $query->where(new \Zend\Db\Sql\Predicate\Like('user_master.name', $whereParams['name']));
            }            
            $query = $query->where(array('user_master.status' => 1));
            if(!empty($optional['pagination'])) {
                $startLimit = ($optional['page']-1)*PER_PAGE_LIMIT;
                $query->limit(PER_PAGE_LIMIT)->offset($startLimit);
            }   
            
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            
            return $result;
        } catch (\Exception $ex) {
            return false;
        } 
    }
    
    function updateUser($params, $where) {
        try {
            if(!empty($where)) {
                $query = $this->sql->update('user_master')
                            ->set($params)
                            ->where($where);
                $satements = $this->sql->prepareStatementForSqlObject($query);
                $result = $satements->execute();
                return true;
            }else {
                return false;
            }
        } catch (\Exception $ex) {
            return false;
        }        
    }
    function addUser($params) {
        try {
            $query = $this->sql->insert('user_master')
                        ->values($params);
            //echo $query->getSqlString();die;
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $this->adapter->getDriver()->getLastGeneratedValue();
        } catch (\Exception $ex) {
            return false;
        }        
    }
    
    function addDeliveryAddress($params) {
        try {
            $query = $this->sql->insert('delivery_address')
                        ->values($params);
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $this->adapter->getDriver()->getLastGeneratedValue();
        } catch (\Exception $ex) {
            return false;
        }        
    }
    
    function updateDeliveryAddress($params, $where) {
        try {
            if(!empty($where)) {
                $query = $this->sql->update('delivery_address')
                            ->set($params)
                            ->where($where);
                $satements = $this->sql->prepareStatementForSqlObject($query);
                $result = $satements->execute();
                return true;
            }else {
                return false;
            }
        } catch (\Exception $ex) {
            return false;
        }                
    }
    
    function getAddressList($where) {
        try {
            $query = $this->sql->select('delivery_address');
            if(!empty($where['id'])) {
                $query = $query->where(array('id'=>$where['id']));
            }            
            if(!empty($where['user_id'])){
                $query = $query->where(array('user_id'=>$where['user_id']));
            }
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $result;
        } catch (\Exception $ex) {
            return false;
        }        
    }
}
