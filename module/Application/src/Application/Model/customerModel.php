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
    function placeOrder($parameters) {
        
    }
    function updateOrderSeq($orderName) {
        try {
            $response = array();
            if(!empty($orderName)) {
                $query = $this->sql->select('order_seq');
                $query = $query->where(array('order_name'=>$orderName));
                $satements = $this->sql->prepareStatementForSqlObject($query);
                $result = $satements->execute()->current();
                if(!empty($result)) {
                    $response[$orderName] = $params['seq'] = $result['seq']+1;
                    $where = array('order_name'=>$orderName);
                    if(!empty($where)) {
                        $query = '';
                        $query = $this->sql->update('order_seq')
                                    ->set($params)
                                    ->where($where);
                        $satements = $this->sql->prepareStatementForSqlObject($query);
                        $result = $satements->execute();
                    }
                }else{
                    $orderData = array();
                    $orderData['order_name'] = $orderName;
                    $response[$orderName] = $orderData['seq'] = 1;
                    $query = $this->sql->insert('order_seq')
                        ->values($orderData);
                    $satements = $this->sql->prepareStatementForSqlObject($query);
                    $result = $satements->execute();
                }
                return $response;                
            }else {
                return false;
            }
        }  catch (\Exception $ex) {
            echo $ex->getMessage();die;
            return false;
        }
    }
    
    function createOrder($orderData) {
        try {
            $query = $this->sql->insert('order_master')
                        ->values($orderData);
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $this->adapter->getDriver()->getLastGeneratedValue();
        } catch (\Exception $ex) {
            return false;
        }         
    }
 
    function orderList($where) {
        try {
            $query = $this->sql->select('order_master');
            if(!empty($where['user_id'])) {
                $query = $query->where(array('user_id'=>$where['user_id']));
            }            
            if(!empty($where['order_status'])){
                $query = $query->where(array('order_status'=>$where['order_status']));
            }
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $result;
        } catch (\Exception $ex) {
            return false;
        }        
    }
    
    function getOrderItem($where) {
        try {
            $query = $this->sql->select('order_items');
            $query = $query->where(array('order_id'=>$where['order_id']));
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $result;
        } catch (\Exception $ex) {
            return false;
        }        
    }
    function smsqueue($params) {
        try {
            $query = $this->sql->insert('sms_queue')
                        ->values($params);
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $this->adapter->getDriver()->getLastGeneratedValue();
        } catch (\Exception $ex) {
            return false;
        }        
    }
    
    function updatesmsfromusmsqueue($param,$where) {
       
        try {            
            $query = $this->sql->update('sms_queue')
                        ->set($param)
                        ->where(array('id'=>$where));
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute()->getAffectedRows();
            return $result;
        } catch (\Exception $ex) {
            return false;
        }
        
    }
    
    function deleteSmsFromQueue($param) {
        try {
            $query = $this->sql->delete('sms_queue')
                            ->where(array('mobile_number'=>$param));
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $result;
        } catch (\Exception $ex) {
            return false;
        } 
        
    }
    
    function checksmsexist($param) {
        try {
            $limit = 1;
            $where = new \Zend\Db\Sql\Where();
            $query = $this->sql->select('sms_queue');
            if(isset($param['mobile_number'])) {
                $query = $query->where(array('sms_queue.mobile_number'=>$param['mobile_number']));
            }
            if(isset($param['otp'])) {
                $query = $query->where(array('sms_queue.otp'=>$param['otp']));
            }
            $query->limit($limit);
            $query->order('id DESC'); 
//            echo $query->getSqlString();die;
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $result;
        } catch (\Exception $ex) {
            return false;
        } 
        
    }
    
    function saveuserauthlink($params) {
        try {
            $query = $this->sql->insert('user_auth')
                        ->values($params);
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $this->adapter->getDriver()->getLastGeneratedValue();
        } catch (\Exception $ex) {
            return false;
        }        
    }
    
    function checkauthkey($param) {
        try {
            $where = new \Zend\Db\Sql\Where();
            $query = $this->sql->select('user_auth');
            if(isset($param['auth_key'])) {
                $query = $query->where(array('user_auth.auth_key'=>$param['auth_key']));
            }
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $result;
        } catch (\Exception $ex) {
            return false;
        } 
        
    }
    
    function insertProductIntoOrderItem($itemData) {
        try {
            $query = $this->sql->insert('order_items')
                        ->values($itemData);    
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $this->adapter->getDriver()->getLastGeneratedValue();
        } catch (\Exception $ex) {
            return false;
        }        
    }    
    function beginTransaction() {
        $this->adapter->getDriver()->getConnection()->beginTransaction();
    }
    function commit() {
        $this->adapter->getDriver()->getConnection()->commit();
    }    
    function rollback() {
        $this->adapter->getDriver()->getConnection()->rollback();
    }     
}
