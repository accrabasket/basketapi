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
            if(!empty($optional['guest_user_id'])) {
                $query = $query->where(array('guest_user_id'=>$optional['guest_user_id']));
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
 
    function updateOrder($params, $where) {
        try {
            if(!empty($where) && !empty($params)) {
                $query = $this->sql->update('order_master')
                            ->set($params)
                            ->where($where);
                $satements = $this->sql->prepareStatementForSqlObject($query);
                $result = $satements->execute()->getAffectedRows();
            }
            return $result;
        } catch (\Exception $ex) {
            return false;
        }         
    }
    
    function orderList($where, $optional=array()) {
        try {
            $query = $this->sql->select('order_master');
            if(!empty($optional['columns'])) {
                $query->columns($optional['columns']);
            }
            if(!empty($where['order_id'])) {
                $query = $query->where(array('order_id'=>$where['order_id']));
            }            
            if(!empty($where['user_id'])) {
                $query = $query->where(array('user_id'=>$where['user_id']));
            }else {
                $query = $query->where(new \Zend\Db\Sql\Predicate\NotLike('order_id', 'order_p%'));
            }            
            if(!empty($where['order_status'])){
                $query = $query->where(array('order_status'=>$where['order_status']));
            }
            if(!empty($optional['pagination'])) {
                $startLimit = ($optional['page']-1)*PER_PAGE_LIMIT;
                $query->limit(PER_PAGE_LIMIT)->offset($startLimit);
            }            
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            if(!empty($optional['count_row'])) {
                $result = $result->current();
            }
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
    
    function assignedOrderToRider($where, $optional = array()) {
        try {
            $query = $this->sql->select('order_assignments');
            $query = $query->join('order_master', 'order_master.order_id = order_assignments.order_id',array('store_id','shipping_address_id', 'order_status'));
            if(!empty($optional['columns'])) {
                $query->columns($optional['columns']);
            }
            if(!empty($where['order_id'])) {
                $query = $query->where(array('order_assignments.order_id'=>$where['order_id']));
            }            
            if(!empty($where['user_id'])) {
                $query = $query->where(array('order_assignments.rider_id'=>$where['user_id']));
            }                        
            if(!empty($where['order_status'])){
                $query = $query->where(array('order_master.order_status'=>$where['order_status']));
            }
            $query = $query->where(array('order_assignments.status'=>1));
            if(!empty($optional['pagination'])) {
                $startLimit = ($optional['page']-1)*PER_PAGE_LIMIT;
                $query->limit(PER_PAGE_LIMIT)->offset($startLimit);
            }   
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            if(!empty($optional['count_row'])) {
                $result = $result->current();
            }
            
            return $result;
        } catch (\Exception $ex) {
            return false;
        }         
    }

    function assignOrder($params) {
        try {
            $query = $this->sql->insert('order_assignments')
                        ->values($params);
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $this->adapter->getDriver()->getLastGeneratedValue();
        } catch (\Exception $ex) {
            return false;
        }         
    }
    
    function updateOrderAssignment($params, $where) {
        $result = false;
        try {        
            if(!empty($where) && !empty($params)) {            
                $query = $this->sql->update('order_assignments')
                            ->set($params)
                            ->where($where);
                $satements = $this->sql->prepareStatementForSqlObject($query);
                $result = $satements->execute();
                
            }   
            return $result;
        } catch (\Exception $ex) {
            return false;
        }
    }
    function insertIntoOtpMaster($params) {
        try {
            $query = $this->sql->insert('otp_master')
                        ->values($params);
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $this->adapter->getDriver()->getLastGeneratedValue();
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
    
    function deleteOtp($where) {
        try {
            if(!empty($where)) {
                $query = $this->sql->delete('otp_master')
                            ->where($where);
            }
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $result;
        } catch (\Exception $ex) {
            return false;
        } 
        
    }
    
    function verifyOtp($where) {
        try {
            $limit = 1;
            $query = $this->sql->select('otp_master');
            $query->columns(array('count' => new \Zend\Db\Sql\Expression('count(*)')));
            if(isset($where['mobile_number'])) {
                $query = $query->where(array('mobile_number'=>$where['mobile_number']));
            }
            if(isset($where['otp'])) {
                $query = $query->where(array('otp'=>$where['otp']));
            }
            if(isset($where['otp_type'])) {
                $query = $query->where(array('otp_type'=>$where['otp_type']));
            }            
            if(isset($where['expiry_date'])) {
                $query = $query->where("expiry_date >= '$where[expiry_date]'");
            }                        
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute()->current();
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
    function deleteUserAuth($where) {
        try {
            if(!empty($where)) {
                $query = $this->sql->delete('user_auth')
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
    function checkauthkey($param) {
        try {
            $where = new \Zend\Db\Sql\Where();
            $query = $this->sql->select('user_auth');
            if(!empty($param['auth_key'])) {
                $query = $query->where(array('user_auth.auth_key'=>$param['auth_key']));
            }
            if(!empty($param['key_for'])) {
                $query = $query->where(array('user_auth.key_for'=>$param['key_for']));
            }
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute()->current();
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

    function changepassword($params, $where) {
        try {
            if(!empty($where)) {
                $query = $this->sql->update('user_master')
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
    
    function enterDataIntoMailQueue($params) {
        try {
            $query = $this->sql->insert('email_queue')
                        ->values($params);
            $satements = $this->sql->prepareStatementForSqlObject($query);
            $result = $satements->execute();
            return $this->adapter->getDriver()->getLastGeneratedValue();
        } catch (\Exception $ex) {
            return false;
        }        
    }
    function getEmailTemplate($where) {
        try {
                $query = $this->sql->select('email_template'); 
                $query = $query->where($where);
                if(!empty($optional['pagination'])) {
                    $startLimit = ($optional['page']-1)*PER_PAGE_LIMIT;
                    $query->limit(PER_PAGE_LIMIT)->offset($startLimit);
                }   

                $satements = $this->sql->prepareStatementForSqlObject($query);
                $result = $satements->execute();
                if(count($where)) {
                    $result = $result->current();
                }
                return $result;
        } catch (\Exception $ex) {
            echo $ex->getMessage();die;
            return false;
        }         
    }
}
